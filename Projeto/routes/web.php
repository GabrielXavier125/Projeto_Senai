<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Dashboard;
use App\Livewire\Livros\Criar as LivroCriar;
use App\Livewire\Livros\Editar as LivroEditar;
use App\Livewire\Livros\Index as LivrosIndex;
use App\Livewire\Movimentacoes\Index as MovimentacoesIndex;
use App\Livewire\Movimentacoes\Registrar;
use App\Livewire\Notificacoes\Index as NotificacoesIndex;
use App\Livewire\Reservas\Index as ReservasIndex;
use App\Livewire\Reservas\Nova;
use App\Livewire\Usuarios\Index as UsuariosIndex;
use App\Livewire\Usuarios\Criar as UsuarioCriar;
use App\Livewire\Usuarios\Editar as UsuarioEditar;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Autenticação
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Área autenticada
Route::middleware('auth')->group(function () {

    // Dashboard — almoxarife exclusivo; coordenador é redirecionado para livros
    Route::get('/dashboard', Dashboard::class)->name('dashboard')->middleware('role:almoxarife');

    // Livros — almoxarife: CRUD; coordenador: somente leitura
    Route::get('/livros', LivrosIndex::class)->name('livros.index');
    Route::get('/livros/criar', LivroCriar::class)->name('livros.criar')->middleware('role:almoxarife');
    Route::get('/livros/{livro}/editar', LivroEditar::class)->name('livros.editar')->middleware('role:almoxarife');

    // Movimentações — almoxarife exclusivo
    Route::middleware('role:almoxarife')->group(function () {
        Route::get('/movimentacoes', MovimentacoesIndex::class)->name('movimentacoes.index');
        Route::get('/movimentacoes/registrar', Registrar::class)->name('movimentacoes.registrar');
    });

    // Reservas — almoxarife gerencia, coordenador cria e acompanha as próprias
    Route::get('/reservas', ReservasIndex::class)->name('reservas.index');
    Route::get('/reservas/nova', Nova::class)->name('reservas.nova')->middleware('role:coordenador');

    // Usuários — almoxarife exclusivo
    Route::middleware('role:almoxarife')->group(function () {
        Route::get('/usuarios', UsuariosIndex::class)->name('usuarios.index');
        Route::get('/usuarios/criar', UsuarioCriar::class)->name('usuarios.criar');
        Route::get('/usuarios/{usuario}/editar', UsuarioEditar::class)->name('usuarios.editar');
    });

    // Notificações — coordenador exclusivo
    Route::get('/notificacoes', NotificacoesIndex::class)->name('notificacoes.index')->middleware('role:coordenador');
});
