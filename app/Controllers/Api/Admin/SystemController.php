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
            
            // Also clear file cache
            $cachePath = WRITEPATH . 'cache/';
            if (is_dir($cachePath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cachePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        @unlink($file->getPathname());
                    } elseif ($file->isDir()) {
                        @rmdir($file->getPathname());
                    }
                }
            }
            
            return $this->success(['cleared' => true], 'Cache cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to clear cache: ' . $e->getMessage(), 500);
        }
    }
}