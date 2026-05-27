<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: adiciona a coluna 'perfil' na tabela 'users'.
 *
 * O perfil define o nível de acesso do usuário no sistema:
 *   - 'almoxarife': acesso operacional (entradas, saídas, cadastro de livros)
 *   - 'coordenador': acesso de gestão (consultas, relatórios, monitoramento)
 *
 * A coluna é nullable para não quebrar usuários já existentes no banco.
 * O seeder garante que todos os usuários ativos tenham um perfil definido.
 */
return new class extends Migration
{
    /**
     * Executa a migration — adiciona a coluna 'perfil'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Coluna enum com os dois perfis possíveis
            // Adicionada após 'password' para organização lógica
            // nullable() evita erro em linhas já existentes na tabela
            $table->enum('perfil', ['almoxarife', 'coordenador'])
                  ->nullable()
                  ->after('password');
        });
    }

    /**
     * Reverte a migration — remove a coluna 'perfil'.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('perfil');
        });
    }
};
