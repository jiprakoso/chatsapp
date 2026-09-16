<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        // user & moderator hanya punya Live Chat — arahkan dashboard ke chat
        $uid  = (int) session()->get('admin_user_id');
        $auth = $uid ? new \App\Services\AuthorizationService() : null;
        $can = static fn(string $perm): bool => $auth !== null && $auth->userHasPermission($uid, $perm);
        $hasDashboard = $can('users.view') || $can('users.manage')
            || $can('roles.manage') || $can('permissions.manage')
            || $can('conversations.view_all') || $can('conversations.manage')
            || $can('system.settings');
        if (! $hasDashboard) {
            return redirect()->to(base_url('chat'));
        }

        return view('webadmin/dashboard');
    }
}