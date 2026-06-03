<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntradaEstoqueRequest;
use App\Http\Requests\SaidaEstoqueRequest;
use App\Models\Livro;
use App\Services\EstoqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    public function __construct(private EstoqueService $estoqueService) {}

    // RF5: entrada de estoque
    public function entrada(EntradaEstoqueRequest $request): JsonResponse
    {
        $livro = Livro::findOrFail($request->livro_id);

        $movimentacao = $this->estoqueService->registrarEntrada(
            livro: $livro,
            quantidade: $request->quantidade,
            usuario: $request->user(),
            observacao: $request->observacao ?? '',
        );

        return response()->json([
            'mensagem' => 'Entrada registrada com sucesso.',
            'saldo_atual' => $livro->fresh()->saldo_atual,
            'livro' => $livro->fresh(),
            'movimentacao' => $movimentacao->load('livro', 'user'),
        ], 201);
    }

    // RF6: saída de estoque (principal do sistema)
    public function saida(SaidaEstoqueRequest $request): JsonResponse
    {
        try {
            $livro = Livro::findOrFail($request->livro_id);

            $movimentacao = $this->estoqueService->registrarSaida(
                livro: $livro,
                quantidade: $request->quantidade,
                usuario: $request->user(),
                observacao: $request->observacao,
            );

            return response()->json([
                'mensagem' => 'Saída registrada com sucesso.',
                'saldo_atual' => $livro->fresh()->saldo_atual,
                'livro' => $livro->fresh(),
                'movimentacao' => $movimentacao->load('livro', 'user'),
            ], 200);

        } catch (\DomainException $e) {
            // RN1: estoque insuficiente
            return response()->json(['mensagem' => $e->getMessage()], 422);
        }
    }

    // RF8: monitoramento de baixo estoque (RN6)
    public function baixoEstoque(Request $request): JsonResponse
    {
        $minimo = $request->has('minimo') ? (int) $request->minimo : null;

        $livros = $this->estoqueService->listarBaixoEstoque($minimo);

        return response()->json([
            'total' => $livros->count(),
            'livros' => $livros,
        ], 200);
    }
}
