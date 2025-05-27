<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    abort(404); 
});

Route::fallback(function () {
    abort(404); 
});
