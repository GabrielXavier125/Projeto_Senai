<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLivroRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:200'],
            'isbn' => ['required', 'string', 'max:20', 'unique:livros,isbn'],
            'materia' => ['required', 'string', 'max:180'],
            'estoque_minimo' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título é obrigatório.',
            'isbn.required' => 'O ISBN é obrigatório.',
            'isbn.unique' => 'Já existe um livro com este ISBN.',
            'materia.required' => 'A matéria é obrigatória.',
        ];
    }
}
