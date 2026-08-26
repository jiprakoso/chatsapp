<?php

if (!function_exists('generateCaptchaImage')) {
    function generateCaptchaImage($question) {
        $width = 300; // Lebar gambar
        $height = 50; // Tinggi gambar
        $image = imagecreatetruecolor($width, $height);

        // Warna latar belakang
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Warna teks
        $textColor = imagecolorallocate($image, 0, 0, 0);

        // Lokasi font
        $font = FCPATH . 'assets/font/code.otf';

        // Ukuran font
        $fontSize = 15;

        // Menghitung bounding box untuk teks
        $bbox = imagettfbbox($fontSize, 0, $font, $question);

        // Menghitung posisi horizontal agar teks berada di tengah
        $textWidth = $bbox[2] - $bbox[0];  // Lebar teks
        $x = round(($width - $textWidth) / 2);      // Posisi horizontal agar teks berada di tengah

        // Menghitung posisi vertikal agar teks berada di tengah
        $textHeight = $bbox[1] - $bbox[7];  // Tinggi teks
        $y = round(($height - $textHeight) / 2 + $textHeight);  // Posisi vertikal agar teks berada di tengah

        // Tulis teks ke gambar
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $font, $question);

        // Simpan gambar sebagai file
        $captchaName = uniqid();
        $imagePath = FCPATH . 'img/captcha/' . $captchaName . '.png';
        imagepng($image, $imagePath);

        // Hapus resource gambar dari memori
        imagedestroy($image);

        session()->set('captcha_image', $imagePath);
        return $imagePath;
    }
}

if (!function_exists('generateCaptchaQuestion')) {
    function generateCaptchaQuestion() {
        $number1 = rand(1, 11); // Angka pertama
        $number2 = rand(1, 11); // Angka kedua
        $operator = rand(0, 2); // Operator: 0 untuk penjumlahan, 1 untuk pengurangan, 2 untuk perkalian

        $operators = ['+', '-', 'x'];
        $selectedOperator = $operators[$operator];

        // Soal dalam bentuk kata
        $question = getNumberInWords($number1) . ' ' . $selectedOperator . ' ' . getNumberInWords($number2);

        // Default nilai $answer untuk menghindari error
        $answer = 0;

        // Hitung jawaban berdasarkan operator
        switch ($selectedOperator) {
            case '+':
                $answer = $number1 + $number2;
                break;
            case '-':
                $answer = $number1 - $number2;
                break;
            case 'x':
                $answer = $number1 * $number2;
                break;
            default:
                // Penanganan kasus tak terduga
                throw new \Exception('Operator tidak valid');
        }

        return [
            'question' => $question,
            'answer' => encrypt($answer)
        ];
    }
}

if (!function_exists('generate_captcha')) {
    function generate_captcha() {
        $captchaNumber = rand(1000, 9999);
        // Store the number in session
        session()->set('captcha', $captchaNumber);
        // Create the CAPTCHA image
        $image = imagecreatetruecolor(100, 40);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 100, 40, $backgroundColor);
        imagestring($image, 5, 30, 10, $captchaNumber, $textColor);
        // Output the image
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}

if (!function_exists('verify_captcha')) {
    function verify_captcha($inputCaptcha) {
        $sessionCaptcha = session()->get('captcha');
        return $inputCaptcha === $sessionCaptcha;
    }
}
