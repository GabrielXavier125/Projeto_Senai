<?php

namespace App\Http\Controllers;

use App\Models\Movimentacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovimentacaoController extends Controller
{
    // RF9: histórico com filtros por livro, período, tipo e usuário
    public function index(Request $request): JsonResponse
    {
        $query = Movimentacao::with(['livro', 'user']);

        if ($request->filled('livro_id')) {
            $query->where('livro_id', $request->livro_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', strtolower($request->tipo));
        }

        if ($request->filled('data_inicio')) {
            $query->where('data_hora', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_hora', '<=', $request->data_fim . ' 23:59:59');
        }

        $movimentacoes = $query->orderBy('data_hora', 'desc')->paginate(20);

        return response()->json($movimentacoes, 200);
    }
}
