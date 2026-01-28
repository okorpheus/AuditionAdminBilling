<?php

namespace App\Http\Controllers;

class ClientController extends Controller
{
    public function index()
    {
        return view('clients.index');
    }
}
