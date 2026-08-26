<?php

if (!function_exists('cek_akses_view')) {
    function cek_akses_view()
    {
        // Cek sesi atau kondisi lainnya
        if (!session()->has('akses_index')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Fungsi ini hanya bisa diakses setelah mengakses halaman index.');
        }
    }
}
