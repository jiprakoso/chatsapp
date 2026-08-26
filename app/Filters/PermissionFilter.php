<?php

namespace App\Filters;

use App\Services\AuthorizationService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Dipakai lewat route filter argument, contoh:
 *   ['filter' => 'permission:users.ban']
 *
 * Harus dijalankan setelah JwtAuthFilter (butuh currentUser sudah terisi).
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $currentUser = service('currentUser');

        if (! $currentUser->isAuthenticated()) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Unauthenticated.']);
        }

        $requiredPermission = $arguments[0] ?? null;

        if ($requiredPermission === null) {
            return service('response')
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'PermissionFilter membutuhkan argument nama permission.']);
        }

        $authorizationService = new AuthorizationService();

        if (! $authorizationService->userHasPermission($currentUser->id(), $requiredPermission)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengakses resource ini.']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
