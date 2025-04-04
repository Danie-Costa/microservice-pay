<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GalleryController;

Route::get('/', function () {
    return response()->json([
        'status' => 'success'
    ]);
});

// Rotas para arquivos
Route::prefix('files')->group(function () {
    Route::post('/', [FileController::class, 'store']); // Criar arquivo
    Route::get('/', [FileController::class, 'index']); // Listar arquivos por token e parâmetros opcionais
    Route::delete('/{id}', [FileController::class, 'destroy']); // Deletar arquivo por ID + token
});

// Rotas para imagens
Route::prefix('images')->group(function () {
    Route::post('/', [ImageController::class,'store']); // Criar imagem
    Route::get('/', [ImageController::class,'index']); // Listar imagens por token e parâmetros opcionais
    Route::delete('/{id}', [ImageController::class,'destroy']); // Deletar imagem por ID + token
});

// Rotas para galerias
Route::prefix('galleries')->group(function () {
    Route::post('/', [GalleryController::class,'store']); // Criar galeria
    Route::get('/', [GalleryController::class,'index']); // Listar galerias por token e parâmetros opcionais
    Route::delete('/{id}', [GalleryController::class,'destroy']); // Deletar galeria por ID + token
});

// Rota especial: listar todos os arquivos, imagens e galerias por token + external_reference
Route::get('/all', 'ProjectAssetsController@index'); 
