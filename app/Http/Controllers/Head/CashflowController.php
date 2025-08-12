<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;

class CashflowController extends Controller
{
    public function index()
    {
        return view('head.cashflow');
    }
}
