<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;

class CashflowController extends Controller
{
    public function index()
    {
        return view('branch.cashflow');
    }
}


