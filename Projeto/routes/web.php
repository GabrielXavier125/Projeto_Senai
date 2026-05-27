<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — SenaiStock
|--------------------------------------------------------------------------
|
| Aqui ficam as rotas acessadas pelo navegador (sessão/cookie).
| A API RESTful ficará em routes/api.php (implementada em sprints futuras).
|
*/

// Rota raiz: redireciona para a tela de login
Route::get('/', function () {
    return redirect()->route('login');
});

// Exibe a tela de login
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');

// Processa o formulário de login (POST)
Route::post('/login', [LoginController::class, 'login']);

// Encerra a sessão do usuário
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
