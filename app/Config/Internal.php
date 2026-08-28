<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Secret untuk service-to-service call dari Node socket-server ke endpoint
 * internal CI4 (mis. ambil FCM token lintas-user). Terpisah dari JWT_SECRET
 * karena ini bukan otentikasi atas nama satu user — Node tidak "login sebagai"
 * siapa pun, ia adalah layanan lain yang dipercaya.
 */
class Internal extends BaseConfig
{
    public string $apiKey;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = env('INTERNAL_API_KEY', 'change-this-in-env-INTERNAL_API_KEY');
    }
}
