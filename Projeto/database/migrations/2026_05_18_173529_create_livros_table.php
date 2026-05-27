<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: cria a tabela 'livros'.
 *
 * Armazena o catálogo de livros didáticos do SENAI.
 * Cada livro possui saldo em tempo real e um nível mínimo de alerta.
 */
return new class extends Migration
{
    /**
     * Executa a migration — cria a tabela 'livros'.
     */
    public function up(): void
    {
        Schema::create('livros', function (Blueprint $table) {
            $table->id();

            // Título do livro didático (ex: "Matemática Básica")
            $table->string('titulo', 200);

            // ISBN — identificador único internacional do livro (RN3)
            // unique() garante que não existam dois livros com o mesmo ISBN
            $table->string('isbn', 20)->unique();

            // Matéria/disciplina à qual o livro pertence (ex: "Informática")
            $table->string('materia', 188);

            // Quantidade atual em estoque — atualizada a cada entrada/saída
            // Começa em 0 pois ainda não há livros físicos cadastrados
            $table->integer('saldo_atual')->default(0);

            // Quantidade mínima antes de disparar alerta de baixo estoque (RN6)
            // Valor padrão: 10 unidades
            $table->integer('estoque_minimo')->default(10);

            // created_at e updated_at — gerenciados automaticamente pelo Laravel
            $table->timestamps();

            // Índices para buscas frequentes por matéria e título
            $table->index('materia');
            $table->index('titulo');
        });
    }

    /**
     * Reverte a migration — remove a tabela 'livros'.
     */
    public function down(): void
    {
        Schema::dropIfExists('livros');
    }
};
