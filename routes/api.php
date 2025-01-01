<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfTranslationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//Route::post('/translate-pdf', [PdfTranslationController::class, 'translatePdf']);
Route::post('/translate-pdf', [PdfTranslationController::class, 'translatePdf']);
