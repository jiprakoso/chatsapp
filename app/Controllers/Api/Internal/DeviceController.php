<?php

namespace App\Controllers\Api\Internal;

use App\Controllers\Api\ApiBaseController;
use App\Models\UserDeviceModel;

/**
 * Endpoint service-to-service (X-Internal-Key, lihat InternalAuthFilter), dipanggil
 * Node socket-server untuk mengirim FCM push ke member conversation yang offline
 * (Section 23). Tidak dipanggil oleh client user biasa.
 */
class DeviceController extends ApiBaseController
{
    /**
     * GET /api/internal/devices?user_ids=1,2,3 — token FCM aktif milik user-user tsb.
     */
    public function index()
    {
        $raw = $this->request->getGet('user_ids') ?? '';

        $userIds = array_values(array_filter(array_map(
            static fn ($id) => (int) trim($id),
            explode(',', (string) $raw),
        ), static fn ($id) => $id > 0));

        if ($userIds === []) {
            return $this->error('user_ids wajib diisi (comma-separated).', 422);
        }

        $devices = (new UserDeviceModel())->activeTokensForUsers($userIds);

        return $this->success(['devices' => $devices]);
    }

    /**
     * POST /api/internal/devices/deactivate — nonaktifkan token yang dilaporkan
     * FCM sudah tidak valid/unregistered, supaya tidak dicoba lagi push berikutnya.
     * Body: { "tokens": ["...", "..."] }
     */
    public function deactivate()
    {
        $input = $this->input();
        $tokens = is_array($input['tokens'] ?? null) ? $input['tokens'] : [];

        $count = (new UserDeviceModel())->deactivateByTokens($tokens);

        return $this->success(['deactivated_count' => $count]);
    }
}
