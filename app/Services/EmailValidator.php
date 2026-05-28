<?php

namespace App\Services;

class EmailValidator
{
    public static function isValidFormat(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function hasMxRecord(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);

        if (empty($domain)) {
            return false;
        }

        return checkdnsrr($domain, 'MX');
    }

    public static function isDeliverable(string $email): array
    {
        if (empty(trim($email))) {
            return ['valid' => false, 'reason' => 'No email address on record'];
        }

        if (!self::isValidFormat($email)) {
            return ['valid' => false, 'reason' => 'Invalid email format'];
        }

        if (!self::hasMxRecord($email)) {
            return ['valid' => false, 'reason' => 'Email domain does not exist or has no mail server'];
        }

        return ['valid' => true, 'reason' => null];
    }
}