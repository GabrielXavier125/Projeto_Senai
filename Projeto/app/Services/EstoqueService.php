<?php

namespace App\Services;

use App\Enums\TipoMovimentacao;
use App\Models\Livro;
use App\Models\Movimentacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Serviço responsável por toda a lógica de estoque.
 *
 * Centraliza as regras de negócio de entrada e saída de livros,
 * garantindo que todas as operações sejam atômicas (RN5) e
 * rastreáveis (RN4).
 *
 * Regras aplicadas aqui:
 *   RN1 - Estoque não pode ficar negativo
 *   RN2 - Quantidade deve ser positiva (> 0)
 *   RN4 - Toda movimentação registra usuário e timestamp
 *   RN5 - Operações em transação (atômicas)
 *   RN6 - Nível mínimo de estoque para alertas
 */
class EstoqueService
{
    /**
     * Registra a entrada de livros no estoque (abastecimento).
     *
     * Soma a quantidade ao saldo atual do livro e grava
     * a movimentação do tipo ENTRADA em transação.
     *
     * @param  Livro  $livro      O livro que está sendo abastecido
     * @param  int    $quantidade Quantidade de livros recebidos (deve ser > 0)
     * @param  User   $usuario    Usuário autenticado que registra a entrada
     * @param  string $observacao Observação opcional (ex: "NF 1234")
     *
     * @throws \InvalidArgumentException se a quantidade for <= 0 (RN2)
     */
    public function registrarEntrada(
        Livro $livro,
        int $quantidade,
        User $usuario,
        string $observacao = ''
    ): Movimentacao {
        // RN2: quantidade deve ser positiva
        if ($quantidade <= 0) {
            throw new \InvalidArgumentException('A quantidade deve ser maior que zero.');
        }

        // RN5: executa tudo em transação — se qualquer passo falhar,
        // o banco volta ao estado anterior (rollback automático)
        return DB::transaction(function () use ($livro, $quantidade, $usuario, $observacao) {

            // Atualiza o saldo do livro somando a quantidade recebida
            $livro->increment('saldo_atual', $quantidade);

            // Cria o registro da movimentação com todos os dados (RN4)
            return Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => $usuario->id,
                'tipo'       => TipoMovimentacao::Entrada,
                'quantidade' => $quantidade,
                'observacao' => $observacao ?: null,
                'data_hora'  => now(), // timestamp automático (RN4)
            ]);
        });
    }

    /**
     * Registra a saída de livros do estoque (baixa manual).
     *
     * Valida o saldo disponível antes de subtrair e grava
     * a movimentação do tipo SAÍDA em transação.
     *
     * @param  Livro  $livro      O livro que está sendo retirado
     * @param  int    $quantidade Quantidade de livros retirados (deve ser > 0)
     * @param  User   $usuario    Usuário autenticado que registra a saída
     * @param  string $observacao Justificativa obrigatória (ex: "Turma T01 - 2026")
     *
     * @throws \InvalidArgumentException se a quantidade for <= 0 (RN2)
     * @throws \DomainException          se o saldo for insuficiente (RN1)
     */
    public function registrarSaida(
        Livro $livro,
        int $quantidade,
        User $usuario,
        string $observacao = ''
    ): Movimentacao {
        // RN2: quantidade deve ser positiva
        if ($quantidade <= 0) {
            throw new \InvalidArgumentException('A quantidade deve ser maior que zero.');
        }

        // RN1: bloqueia saída se não há saldo suficiente
        if (!$livro->temSaldoSuficiente($quantidade)) {
            throw new \DomainException(
                "Estoque insuficiente. Saldo atual: {$livro->saldo_atual} | Solicitado: {$quantidade}."
            );
        }

        // RN5: transação garante que saldo e movimentação ficam sempre sincronizados
        return DB::transaction(function () use ($livro, $quantidade, $usuario, $observacao) {

            // Subtrai a quantidade do saldo do livro
            $livro->decrement('saldo_atual', $quantidade);

            // Cria o registro da movimentação (RN4)
            return Movimentacao::create([
                'livro_id'   => $livro->id,
                'user_id'    => $usuario->id,
                'tipo'       => TipoMovimentacao::Saida,
                'quantidade' => $quantidade,
                'observacao' => $observacao ?: null,
                'data_hora'  => now(),
            ]);
        });
    }

    /**
     * Retorna os livros com saldo abaixo ou igual ao estoque mínimo (RN6).
     *
     * @param  int $minimo Valor mínimo de referência (padrão: usa estoque_minimo do livro)
     */
    public function listarBaixoEstoque(?int $minimo = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Livro::query();

        if ($minimo !== null) {
            // Usa o mínimo informado como parâmetro
            $query->where('saldo_atual', '<=', $minimo);
        } else {
            // Usa o estoque_minimo configurado em cada livro (padrão: 10)
            $query->whereColumn('saldo_atual', '<=', 'estoque_minimo');
        }

        return $query->orderBy('saldo_atual')->get();
    }
}
