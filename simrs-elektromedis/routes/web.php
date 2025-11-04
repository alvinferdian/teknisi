<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeknisiController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('teknisi', TeknisiController::class);
?>