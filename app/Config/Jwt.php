<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Jwt extends BaseConfig
{
    public string $secret;
    public int $ttl = 3600;

    public function __construct()
    {
        parent::__construct();

        $this->secret = env('JWT_SECRET', 'change-this-in-env-JWT_SECRET');
    }
}
