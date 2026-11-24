<?php

declare(strict_types=1);

namespace ContactForm\Security;

use InvalidArgumentException;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

final class TotpVerifier
{
    private const int PERIOD_SECONDS = 30;
    private const int DRIFT_STEPS = 1;

    public function generateSecret(): string
    {
        return Base32::encodeUpperUnpadded(random_bytes(20));
    }

    public function provisioningUri(#[\SensitiveParameter] string $secret, string $label, string $issuer): string
    {
        if ($label === '' || $issuer === '') {
            throw new InvalidArgumentException('A TOTP label and issuer are required.');
        }

        $totp = $this->totp($secret);
        $totp->setLabel($label);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri();
    }

    public function codeAt(#[\SensitiveParameter] string $secret, int $timestamp): string
    {
        return $this->totp($secret)->at(max(0, $timestamp));
    }

    public function matchingStep(
        #[\SensitiveParameter] string $secret,
        #[\SensitiveParameter] string $code,
        ?int $lastAcceptedStep,
        int $now,
    ): ?int {
        if (preg_match('/\A[0-9]{6}\z/', $code) !== 1) {
            return null;
        }

        $totp = $this->totp($secret);
        $currentStep = intdiv($now, self::PERIOD_SECONDS);
        $matched = null;
        for ($step = $currentStep - self::DRIFT_STEPS; $step <= $currentStep + self::DRIFT_STEPS; $step++) {
            if ($step < 0) {
                continue;
            }
            $isMatch = hash_equals($totp->at($step * self::PERIOD_SECONDS), $code);
            if ($isMatch && $matched === null && ($lastAcceptedStep === null || $step > $lastAcceptedStep)) {
                $matched = $step;
            }
        }

        return $matched;
    }

    private function totp(#[\SensitiveParameter] string $secret): TOTP
    {
        if ($secret === '') {
            throw new InvalidArgumentException('A TOTP secret is required.');
        }

        return TOTP::createFromSecret($secret);
    }
}
