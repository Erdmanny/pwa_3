<?php

namespace App\Controllers;


class Fallback extends BaseController
{
    public function index()
    {
        echo view('fallback');
    }


}
