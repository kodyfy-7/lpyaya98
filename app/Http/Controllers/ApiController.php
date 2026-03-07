<?php

namespace App\Http\Controllers;

class ApiController extends Controller
{
    public function hello()
    {
        return response()->json([
            'message' => 'Hello from Laravel 12!',
            'status' => 'success',
        ]);
    }
}
