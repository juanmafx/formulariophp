<?php

declare(strict_types=1);

namespace ContactForm;

use ContactForm\Admin\AdminAuthenticator;
use ContactForm\Contact\AttachmentValidator;
use ContactForm\Contact\ContactService;
use ContactForm\Contact\ContactValidator;
use ContactForm\Contact\SubmissionRepository;
use ContactForm\Contact\SubmissionVault;
use ContactForm\Contact\ValidatedAttachment;
use ContactForm\Http\Request;
use ContactForm\Http\Response;
use ContactForm\Http\SecurityHeaders;
use ContactForm\Security\CsrfTokenManager;
use ContactForm\Security\RateLimiter;
use ContactForm\Security\SessionStore;
use ContactForm\View\TemplateRenderer;

final readonly class Application
{
    private const int PAGE_SIZE = 25;
    private const string FLASH_SUBMITTED = 'contact_submitted';

    public function __construct(
        private ContactValidator $validator,
        private AttachmentValidator $attachments,
        private ContactService $contact,
        private SubmissionRepository $submissions,
        private SubmissionVault $vault,
        private AdminAuthenticator $authenticator,
        private CsrfTokenManager $csrf,
        private RateLimiter $rateLimiter,
        private SessionStore $session,
        private TemplateRenderer $renderer,
        private bool $secure,
    ) {
    }

    public function handle(Request $request): Response
    {
        return SecurityHeaders::apply($this->dispatch($request), $this->secure);
    }

    private function dispatch(Request $request): Response
    {
        if ($request->method === 'POST' && !$this->csrf->isValid($request->input('csrf_token'))) {
            return $this->error(403, 'Your session expired. Reload the page and try again.');
        }

        return match ([$request->method, rtrim($request->path, '/') ?: '/']) {
            ['GET', '/'] => $this->showContactForm(),
            ['POST', '/contact'] => $this->submitContact($request),
            ['GET', '/confirmation'] => $this->showConfirmation(),
            ['GET', '/admin'] => $this->showSubmissions($request),
            ['GET', '/admin/login'] => $this->showLogin(),
            ['POST', '/admin/login'] => $this->login($request),
            ['GET', '/admin/verify'] => $this->showVerify(),
            ['POST', '/admin/verify'] => $this->verify($request),
            ['POST', '/admin/logout'] => $this->logout(),
            default => $this->error(404, 'Page not found.'),
        };
    }

    private function showContactForm(array $errors = [], array $values = [], int $status = 200): Response
    {
        return $this->page('contact', [
            'errors' => $errors,
            'values' => $values,
            'csrfToken' => $this->csrf->token(),
            'maxBytes' => AttachmentValidator::MAX_BYTES,
        ], 'Contact form', $status);
    }

    private function submitContact(Request $request): Response
    {
        if (!$this->rateLimiter->allow('contact', $request->clientIp, 10, 3600)) {
            return $this->error(429, 'Too many messages. Try again later.');
        }

        $fields = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
        ];
        $result = $this->validator->validate($fields);
        $errors = $result->errors;

        $attachment = null;
        if ($request->attachment !== null) {
            $validated = $this->attachments->validate($request->attachment);
            if ($validated instanceof ValidatedAttachment) {
                $attachment = $validated;
            } else {
                $errors['attachment'] = $validated;
            }
        }

        if ($errors !== [] || $result->input === null) {
            return $this->showContactForm($errors, $fields, 422);
        }

        $this->contact->submit($result->input, $attachment);
        $this->session->set(self::FLASH_SUBMITTED, true);

        return Response::redirect('/confirmation');
    }

    private function showConfirmation(): Response
    {
        if ($this->session->get(self::FLASH_SUBMITTED) !== true) {
            return Response::redirect('/');
        }
        $this->session->remove(self::FLASH_SUBMITTED);

        return $this->page('confirmation', [], 'Message received');
    }

    private function showLogin(?string $error = null, int $status = 200): Response
    {
        if ($this->authenticator->isAuthenticated()) {
            return Response::redirect('/admin');
        }

        return $this->page('admin/login', ['error' => $error, 'csrfToken' => $this->csrf->token()], 'Admin sign-in', $status);
    }

    private function login(Request $request): Response
    {
        $username = mb_strtolower(trim($request->input('username')));
        $withinIpLimit = $this->rateLimiter->allow('login-ip', $request->clientIp, 10, 900);
        $withinAccountLimit = $this->rateLimiter->allow('login-account', $username, 10, 900);
        if (!$withinIpLimit || !$withinAccountLimit) {
            return $this->error(429, 'Too many sign-in attempts. Try again later.');
        }

        if (!$this->authenticator->attemptPassword($username, $request->input('password'))) {
            return $this->showLogin('Invalid credentials.', 401);
        }

        return Response::redirect('/admin/verify');
    }

    private function showVerify(?string $error = null, int $status = 200): Response
    {
        if ($this->authenticator->pendingAdminId() === null) {
            return Response::redirect('/admin/login');
        }

        return $this->page('admin/verify', ['error' => $error, 'csrfToken' => $this->csrf->token()], 'Two-factor verification', $status);
    }

    private function verify(Request $request): Response
    {
        $pendingId = $this->authenticator->pendingAdminId();
        if ($pendingId === null) {
            return Response::redirect('/admin/login');
        }
        if (!$this->rateLimiter->allow('totp', (string) $pendingId, 5, 300)) {
            return $this->error(429, 'Too many verification attempts. Try again later.');
        }
        if (!$this->authenticator->verifySecondFactor(trim($request->input('code')))) {
            return $this->showVerify('Invalid or already used code.', 401);
        }

        return Response::redirect('/admin');
    }

    private function logout(): Response
    {
        $this->authenticator->logout();

        return Response::redirect('/admin/login');
    }

    private function showSubmissions(Request $request): Response
    {
        if (!$this->authenticator->isAuthenticated()) {
            return Response::redirect('/admin/login');
        }

        $total = $this->submissions->count();
        $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($request->queryInt('page', 1), $pageCount);
        $stored = $this->submissions->findPage(self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE);

        return $this->page('admin/submissions', [
            'submissions' => array_map($this->vault->open(...), $stored),
            'page' => $page,
            'pageCount' => $pageCount,
            'total' => $total,
            'csrfToken' => $this->csrf->token(),
        ], 'Submissions');
    }

    private function error(int $status, string $message): Response
    {
        return $this->page('error', ['message' => $message], 'Error', $status);
    }

    private function page(string $template, array $data, string $title, int $status = 200): Response
    {
        return new Response($status, $this->renderer->render($template, $data, $title), [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }
}
