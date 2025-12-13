<?php

namespace App\Validation;

class UserRules
{
    /**
     * Check if password has 1 number and 1 letter.
     */
    public function validate_password_strength(string $str, string &$error = null): bool
    {
        if (!preg_match('/\d/', $str) || !preg_match('/[a-zA-Z]/', $str)) {
            $error = 'Password must contain at least one letter and one number';
            return false;
        }
        return true;
    }
}