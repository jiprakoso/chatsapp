<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\Api\Admin\AdminBaseController;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class SystemController extends AdminBaseController
{
    public function clearCache()
    {
        try {
            $cache = Services::cache();
            $cache->clean();

            // Juga bersihkan file cache bila handler saat ini bukan file
            // (Valkey/redis) — ada fallback yang file-nya juga bisa numpuk.
            $cachePath = WRITEPATH . 'cache/';
            if (is_dir($cachePath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cachePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $file) {
                    if ($file->isFile() && ! str_starts_with($file->getFilename(), '.')) {
                        @unlink($file->getPathname());
                    } elseif ($file->isDir()) {
                        @rmdir($file->getPathname());
                    }
                }
            }

            return $this->success(['cleared' => true]);
        } catch (\Exception $e) {
            return $this->error('Failed to clear cache: ' . $e->getMessage(), 500);
        }
    }
}