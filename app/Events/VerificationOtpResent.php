<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class VerificationOtpResent
{
    use Dispatchable;

    public function __construct(
        public string $email,
        public string $verificationLink,
    ) {}
}
