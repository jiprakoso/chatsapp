<?php

namespace App\Controllers\WebAdmin;

use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        return view('webadmin/settings/index');
    }
}