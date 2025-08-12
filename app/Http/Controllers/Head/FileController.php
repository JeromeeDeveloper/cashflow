<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;

class FileController extends Controller
{
    public function index()
    {
        return view('head.file');
    }
}
