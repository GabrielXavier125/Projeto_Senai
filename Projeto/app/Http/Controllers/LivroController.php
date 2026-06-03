<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLivroRequest;
use App\Models\Livro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LivroController extends Controller
{
    // RF4: listagem com paginação e filtros por título/ISBN/matéria
    public function index(Request $request): JsonResponse
    {
        $query = Livro::query();

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('isbn')) {
            $query->where('isbn', 'like', '%' . $request->isbn . '%');
        }

        if ($request->filled('materia')) {
            $query->where('materia', 'like', '%' . $request->materia . '%');
        }

        $livros = $query->orderBy('titulo')->paginate(15);

        return response()->json($livros, 200);
    }

    // RF3: cadastro de livro com ISBN único
    public function store(StoreLivroRequest $request): JsonResponse
    {
        $livro = Livro::create($request->validated());

        return response()->json($livro, 201);
    }

    // RF7: dados do livro + saldo atual
    public function show(int $id): JsonResponse
    {
        $livro = Livro::findOrFail($id);

        return response()->json($livro, 200);
    }

    // RF7: consulta apenas o saldo atual
    public function saldo(int $id): JsonResponse
    {
        $livro = Livro::findOrFail($id);

        return response()->json([
            'livro_id' => $livro->id,
            'titulo' => $livro->titulo,
            'isbn' => $livro->isbn,
            'saldo_atual' => $livro->saldo_atual,
            'estoque_minimo' => $livro->estoque_minimo,
            'baixo_estoque' => $livro->estaBaixoEstoque(),
        ], 200);
    }
}
