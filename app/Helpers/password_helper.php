<?php

if (!function_exists('validate_password')) {
    /**
     * Validates a password based on the following criteria:
     * - Minimum 8 characters
     * - Maximum 16 characters
     * - At least 1 capital letter
     * - At least 1 symbol
     * - At least 1 letter
     *
     * @param string $password
     * @return bool
     */
    function validate_password($password)
    {
        // Check if the password meets the length requirements
        if (strlen($password) < 8 || strlen($password) > 16) {
            return false;
        }

        // Check if the password contains at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check if the password contains at least one letter (uppercase or lowercase)
        if (!preg_match('/[a-zA-Z]/', $password)) {
            return false;
        }

        // Check if the password contains at least one symbol
        if (!preg_match('/[\W_]/', $password)) {
            return false;
        }

        return true;
    }
}
