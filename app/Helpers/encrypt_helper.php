<?php
use Config\Encryption;

if (!function_exists('encrypt')) {
    function encrypt($data = '', $key = NULL)
    {
        if ($key === NULL) {
            $CI = config(Encryption::class);;
            $key = $CI->key;
        }

        $cipher = "AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $ciphertext_raw = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);

        $ciphertext = $iv . $hmac . $ciphertext_raw;
        $ciphertext_base64 = base64_encode($ciphertext);

        $ciphertext_base64 = str_replace(array('+', '/'), array('-', '_'), $ciphertext_base64);

        return $ciphertext_base64;
    }
}

if (!function_exists('encrypt_data')) {
    function encrypt_data($data = NULL, $key = TRUE)
    {
        if (empty($data)) {
            return NULL;
        }

        $return = [];
        foreach ($data as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $value = encrypt_data($v, $key);
            } else {
                $value = encrypt($v);
            }
            $return[$key ? encrypt($k) : $k] = $value;
        }
        return $return;
    }
}
