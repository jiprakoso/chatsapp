<?php

namespace App\Controllers\Api;

use App\Models\UserDeviceModel;

class DeviceController extends ApiBaseController
{
    /**
     * POST /api/devices — registrasi/refresh FCM token milik user login.
     * Body: { "platform": "android|ios|web", "fcm_token": "..." }
     */
    public function register()
    {
        $input = $this->input();

        $rules = [
            'platform'  => 'required|in_list[android,ios,web]',
            'fcm_token' => 'required',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal.', 422, $this->validator->getErrors());
        }

        $device = (new UserDeviceModel())->registerDevice(
            $this->currentUserId(),
            $input['platform'],
            $input['fcm_token'],
        );

        return $this->success($device, 201);
    }

    /**
     * DELETE /api/devices/(:num) — nonaktifkan device milik sendiri (mis. saat logout).
     */
    public function delete($id = null)
    {
        $deviceModel = new UserDeviceModel();
        $device      = $deviceModel->find((int) $id);

        if ($device === null || (int) $device['user_id'] !== $this->currentUserId()) {
            return $this->error('Device tidak ditemukan.', 404);
        }

        $deviceModel->deactivateOwnDevice((int) $id, $this->currentUserId());

        return $this->success(['deactivated' => true]);
    }
}
