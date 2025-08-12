<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('head.dashboard');
    }
}
