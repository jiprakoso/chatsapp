<?php

use CodeIgniter\Database\BaseConnection;

if (!function_exists('clean_attendance_photos')) {
    /**
     * Bersihkan foto attendance yang tidak ada di DB
     *
     * @param bool $deleteMode true = hapus, false = cuma ngelist
     * @param string $logFile lokasi file log
     */
    function clean_attendance_photos($deleteMode = false, $logFile = WRITEPATH . 'logs/missing_photos.log') {
        $db = \Config\Database::connect();

        // Ambil semua data foto dari DB
        $photosInDb = [];
        $batchSize = 500000;
        $offset = 0;

        do {
            $builder = $db->table('attendance_logs')
                ->select('photo')
                ->limit($batchSize, $offset)
                ->get();

            $rows = $builder->getResultArray();
            foreach ($rows as $row) {
                $photosInDb[$row['photo']] = true;
            }

            $offset += $batchSize;
        } while (count($rows) > 0);

        log_message('info', "Total foto valid di DB: " . count($photosInDb));

        // Scan folder attphoto
        $path = FCPATH . 'attphoto';
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $deleted = 0;
        $listed  = 0;
        $fp = fopen($logFile, 'a');

        foreach ($rii as $file) {
            if ($file->isDir()) continue;

            $relative = 'attphoto/' . $file->getFilename();

            if (!isset($photosInDb[$relative])) {
                if ($deleteMode) {
                    if (@unlink($file->getPathname())) {
                        $deleted++;
                        fwrite($fp, "[DELETED] " . $file->getPathname() . PHP_EOL);
                    }
                } else {
                    $listed++;
                    fwrite($fp, "[MISSING] " . $file->getPathname() . PHP_EOL);
                }
            }
        }

        fclose($fp);

        if ($deleteMode) {
            log_message('info', "Total foto dihapus: $deleted");
        } else {
            log_message('info', "Total foto yang tidak ada di DB (sudah dicatat di log): $listed");
        }
    }
}
