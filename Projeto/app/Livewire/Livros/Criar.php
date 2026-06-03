<?php

namespace App\Livewire\Livros;

use App\Models\Livro;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Novo Livro')]
class Criar extends Component
{
    public string $titulo         = '';
    public string $isbn           = '';
    public string $materia        = '';
    public int    $estoque_minimo = 10;

    protected function rules(): array
    {
        return [
            'titulo'         => ['required', 'string', 'max:200'],
            'isbn'           => ['required', 'string', 'max:20', 'unique:livros,isbn'],
            'materia'        => ['required', 'string', 'max:180'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'titulo.required'  => 'O título é obrigatório.',
            'isbn.required'    => 'O ISBN é obrigatório.',
            'isbn.unique'      => 'Já existe um livro com este ISBN.',
            'materia.required' => 'A matéria é obrigatória.',
        ];
    }

    public function salvar()
    {
        $data = $this->validate();
        Livro::create($data);
        $this->dispatch('notify', type: 'success', message: 'Livro cadastrado com sucesso!');
        return $this->redirect(route('livros.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.livros.criar');
    }
}
