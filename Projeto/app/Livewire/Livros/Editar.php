<?php

namespace App\Livewire\Livros;

use App\Models\Livro;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Editar Livro')]
class Editar extends Component
{
    public Livro  $livro;
    public string $titulo         = '';
    public string $isbn           = '';
    public string $materia        = '';
    public int    $estoque_minimo = 10;

    public function mount(Livro $livro): void
    {
        $this->livro          = $livro;
        $this->titulo         = $livro->titulo;
        $this->isbn           = $livro->isbn;
        $this->materia        = $livro->materia;
        $this->estoque_minimo = $livro->estoque_minimo;
    }

    protected function rules(): array
    {
        return [
            'titulo'         => ['required', 'string', 'max:200'],
            'isbn'           => ['required', 'string', 'max:20', "unique:livros,isbn,{$this->livro->id}"],
            'materia'        => ['required', 'string', 'max:180'],
            'estoque_minimo' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'titulo.required'  => 'O título é obrigatório.',
            'isbn.required'    => 'O ISBN é obrigatório.',
            'isbn.unique'      => 'Já existe outro livro com este ISBN.',
            'materia.required' => 'A matéria é obrigatória.',
        ];
    }

    public function salvar()
    {
        $data = $this->validate();
        $this->livro->update($data);
        $this->dispatch('notify', type: 'success', message: 'Livro atualizado com sucesso!');
        return $this->redirect(route('livros.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.livros.editar');
    }
}
