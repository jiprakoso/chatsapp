<?php

if (!function_exists('truncate_text')) {
    function truncate_text($text, $maxLength) {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength) . '...';
        }
        return $text;
    }
}
