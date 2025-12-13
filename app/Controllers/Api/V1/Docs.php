<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

class Docs extends BaseController
{
    public function index()
    {
        return view('swagger');
    }
}