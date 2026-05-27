<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration que cria a tabela de reservas de livros.
 *
 * Uma reserva representa a intenção do coordenador de retirar
 * um conjunto de livros do almoxarifado. O almoxarife visualiza
 * as reservas pendentes e dá baixa quando os livros são entregues.
 *
 * A reserva pode ser criada mesmo com estoque insuficiente —
 * nesse caso, um aviso é gerado para o almoxarife (RN7).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            // Livro que está sendo reservado
            $table->foreignId('livro_id')
                ->constrained('livros')
                ->restrictOnDelete(); // impede excluir livro com reservas

            // Coordenador que fez a reserva
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Quantidade de exemplares solicitados
            $table->unsignedInteger('quantidade');

            // Status do ciclo de vida da reserva
            $table->enum('status', ['pendente', 'retirada', 'cancelada'])
                ->default('pendente');

            // Justificativa/turma informada pelo coordenador
            $table->text('observacao')->nullable();

            // Data/hora em que a reserva foi criada
            $table->timestamp('data_reserva');

            // Data/hora em que o almoxarife deu baixa (null enquanto pendente)
            $table->timestamp('data_retirada')->nullable();

            $table->timestamps();

            // Índices para consultas frequentes
            $table->index('status');
            $table->index('livro_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
