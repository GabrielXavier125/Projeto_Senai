<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: cria a tabela 'movimentacoes'.
 *
 * Registra o histórico completo de entradas e saídas de livros.
 * Cada linha representa uma operação realizada por um usuário,
 * garantindo rastreabilidade total (RN4).
 */
return new class extends Migration
{
    /**
     * Executa a migration — cria a tabela 'movimentacoes'.
     */
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();

            // Qual livro foi movimentado (RN4)
            // onDelete('restrict') impede excluir livro com histórico
            $table->foreignId('livro_id')
                  ->constrained('livros')
                  ->onDelete('restrict');

            // Quem realizou a operação (RN4)
            // onDelete('restrict') impede excluir usuário com histórico
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');

            // Tipo da movimentação: 'entrada' ou 'saida'
            $table->enum('tipo', ['entrada', 'saida']);

            // Quantidade movimentada — sempre positiva (RN2)
            // unsigned garante que não aceita negativos no banco
            $table->unsignedInteger('quantidade');

            // Justificativa da operação (turma, motivo etc.)
            // Recomendado para saídas (RF6)
            $table->text('observacao')->nullable();

            // Data e hora exata da operação (RN4)
            $table->timestamp('data_hora');

            // created_at e updated_at automáticos do Laravel
            $table->timestamps();

            // Índices para filtros e relatórios frequentes
            $table->index('tipo');
            $table->index('data_hora');
            $table->index('livro_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverte a migration — remove a tabela 'movimentacoes'.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
