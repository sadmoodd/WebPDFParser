<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutHelpController extends Controller
{
    public function about(){
        return view('about');
    }

    public function help(){
        return view('help');
    }
}
