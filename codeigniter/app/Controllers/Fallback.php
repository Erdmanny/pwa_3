<?php

namespace App\Controllers;


class Fallback extends BaseController
{
    /**
     * @return string - Fallbackpage View
     */
    public function index(): string
    {
        return view('fallback');
    }


}
