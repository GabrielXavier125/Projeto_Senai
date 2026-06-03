<?php

namespace Database\Seeders;

use App\Enums\PerfilUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder principal — orquestra todos os seeders do projeto.
 *
 * Ordem de execução (importante — respeitar dependências):
 *   1. Usuários  → criados aqui diretamente (base para os demais)
 *   2. Livros    → LivroSeeder (independente dos usuários)
 *   3. Movimentações → MovimentacaoSeeder (depende de livros e usuários)
 *
 * Para popular o banco: php artisan db:seed
 * Para resetar e repopular: php artisan migrate:fresh --seed
 *
 * Credenciais de acesso (apenas para desenvolvimento):
 *   almoxarife@senai.br  / senha123  → Almoxarife
 *   coordenador@senai.br / senha123  → Coordenador
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Executa todos os seeders na ordem correta.
     */
    public function run(): void
    {
        // ── 1. Usuários de teste ──────────────────────────────────────────────

        // Almoxarife — acesso operacional (cadastra livros, registra movimentações)
        User::updateOrCreate(
            ['email' => 'almoxarife@senai.br'],
            [
                'name'     => 'Carlos Almoxarife',
                'password' => Hash::make('senha123'),
                'perfil'   => PerfilUsuario::Almoxarife,
            ]
        );

        // Professores — cada um responsável por uma matéria
        User::updateOrCreate(
            ['email' => 'coordenador@senai.br'],
            [
                'name'     => 'Prof. Ana Silva',
                'password' => Hash::make('senha123'),
                'perfil'   => PerfilUsuario::Coordenador,
                'materia'  => 'Programação',
            ]
        );

        User::updateOrCreate(
            ['email' => 'prof.redes@senai.br'],
            [
                'name'     => 'Prof. Carlos Redes',
                'password' => Hash::make('senha123'),
                'perfil'   => PerfilUsuario::Coordenador,
                'materia'  => 'Redes',
            ]
        );

        User::updateOrCreate(
            ['email' => 'prof.eletro@senai.br'],
            [
                'name'     => 'Prof. Marcos Eletro',
                'password' => Hash::make('senha123'),
                'perfil'   => PerfilUsuario::Coordenador,
                'materia'  => 'Eletrotécnica',
            ]
        );

        // ── 2. Livros didáticos ───────────────────────────────────────────────

        $this->call(LivroSeeder::class);

        // ── 3. Histórico de movimentações ─────────────────────────────────────

        $this->call(MovimentacaoSeeder::class);
    }
}
