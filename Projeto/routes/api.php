<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\MovimentacaoController;
use Illuminate\Support\Facades\Route;

// RF1: Autenticação pública
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Rotas protegidas por token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // RF10: Logout
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // RF3 / RF4 / RF7: Livros
    Route::get('livros', [LivroController::class, 'index']);
    Route::post('livros', [LivroController::class, 'store']);
    Route::get('livros/{id}', [LivroController::class, 'show']);
    Route::get('livros/{id}/saldo', [LivroController::class, 'saldo']);

    // RF5 / RF6 / RF8: Estoque
    Route::post('stock/entries', [EstoqueController::class, 'entrada']);
    Route::post('stock/exits', [EstoqueController::class, 'saida']);
    Route::get('stock/low', [EstoqueController::class, 'baixoEstoque']);

    // RF9: Histórico de movimentações
    Route::get('movimentacoes', [MovimentacaoController::class, 'index']);
});
