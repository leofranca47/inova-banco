<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user/{id}', function (int $id) {
    \App\Models\User::find($id);
});
