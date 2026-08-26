<?php
use Config\Encryption;

if (!function_exists('decrypt')) {
    function decrypt($string = '', $null = TRUE, $key = null)
    {
        $return = FALSE;
        if ($key === NULL) {
            $CI = config(Encryption::class);;
            $key = $CI->key;
        }
        if ($null === FALSE) {
            if ($string === NULL || empty($string)) {
                return NULL;
            }
        }
        if (is_string($string) && strlen($string) >= 86) {
            $string = str_replace(array('-', '_'), array('+', '/'), $string);
            $c = base64_decode($string);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $return = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
            //$original_plaintext=gzuncompress($original_plaintext);
        }
        if (!empty($return) && $return !== FALSE) {
            if (substr($return, 0, 5) == 'bool-') {
                $return = str_replace('bool-', '', $return);
                $return = $return == '1' ? TRUE : FALSE;
            }
        }
        return $return;
    }
}

if (!function_exists('decrypt_data')) {
    function decrypt_data($data = NULL, $key = TRUE, $null = TRUE, $customKey = null)
    {
        if (empty($data)) {
            $return = NULL;
        } else {
            $return = [];
            foreach ($data as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $value = decrypt_data($v, $key, $null, $customKey); // Rekursi dengan customKey
                } else {
                    $value = decrypt($v, $null, $customKey); // Custom key diteruskan
                }
                $return[$key ? decrypt($k, $null, $customKey) : $k] = $value;
            }
        }
        return $return;
    }
}
