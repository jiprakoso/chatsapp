<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        $uid  = (int) session()->get('admin_user_id');
        $auth = $uid ? new \App\Services\AuthorizationService() : null;
        if ($auth === null || ! $auth->userHasPermission($uid, 'system.settings')) {
            return redirect()->to(base_url('chat'));
        }
        return view('webadmin/settings/index');
    }
}