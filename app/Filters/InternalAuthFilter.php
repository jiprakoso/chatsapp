<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Internal;

/**
 * Melindungi endpoint service-to-service (dipanggil Node socket-server, bukan
 * client user biasa) lewat header X-Internal-Key, bukan JWT user.
 */
class InternalAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $given    = $request->getHeaderLine('X-Internal-Key');
        $expected = (new Internal())->apiKey;

        if ($given === '' || ! hash_equals($expected, $given)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Internal API key tidak valid.']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
