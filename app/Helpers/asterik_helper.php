<?php

if (!function_exists('mask_character')) {
    function mask_character($string) {
        $length = strlen($string);
        if ($length <= 4) {
            return str_repeat('*', $length);
        } else {
            return substr($string, 0, $length - 4) . '****';
        }
    }
}
