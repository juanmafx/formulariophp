<?php

declare(strict_types=1);

namespace ContactForm\View;

use RuntimeException;

final readonly class TemplateRenderer
{
    public function __construct(private string $templateDirectory)
    {
    }

    public function render(string $template, array $data = [], string $title = 'Contact'): string
    {
        $content = $this->capture($template, $data);

        return $this->capture('layout', ['title' => $title, 'content' => $content]);
    }

    private function capture(string $template, array $data): string
    {
        $path = $this->templateDirectory . '/' . $template . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Unknown template.');
        }

        $escape = static fn (mixed $value): string => htmlspecialchars(
            is_scalar($value) ? (string) $value : '',
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            'UTF-8',
        );

        ob_start();
        try {
            (static function (string $__path, array $view, callable $e): void {
                require $__path;
            })($path, $data, $escape);

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
