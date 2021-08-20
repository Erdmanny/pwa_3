<?php

namespace App\Controllers;


class Fallback extends BaseController
{
    public function index(): string
    {
        return view('fallback');
    }


}
