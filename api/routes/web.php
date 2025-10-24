<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Pocket Money API is running',
        'version' => '1.0.0',
        'endpoints' => [
            'stocks' => '/api/stocks',
            'news' => '/api/news',
            'user' => '/api/user',
            'auth' => '/api/auth'
        ]
    ]);
});
