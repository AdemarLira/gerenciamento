<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function clinic(): \App\Models\Clinic
    {
        return auth()->user()->clinic;
    }
}
