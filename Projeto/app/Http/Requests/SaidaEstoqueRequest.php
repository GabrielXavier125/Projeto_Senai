<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaidaEstoqueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAlmoxarife() || $user->isCoordenador());
    }

    public function rules(): array
    {
        return [
            'livro_id' => ['required', 'integer', 'exists:livros,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacao' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'livro_id.required' => 'Informe o livro.',
            'livro_id.exists' => 'Livro não encontrado.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.min' => 'A quantidade deve ser maior que zero.',
            'observacao.required' => 'A observação (ex.: turma/justificativa) é obrigatória para saídas.',
        ];
    }
}
