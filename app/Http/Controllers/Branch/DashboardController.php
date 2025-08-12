<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('branch.dashboard');
    }
}
