<?php

namespace App\Livewire\Livros;

use App\Models\Livro;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Livros')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $materia = '';

    public ?int $confirmDelete = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingMateria(): void { $this->resetPage(); }

    public function confirmarExclusao(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function cancelarExclusao(): void
    {
        $this->confirmDelete = null;
    }

    public function excluir(): void
    {
        $livro = Livro::findOrFail($this->confirmDelete);
        try {
            $livro->delete();
            $this->confirmDelete = null;
            $this->dispatch('notify', type: 'success', message: 'Livro excluído com sucesso.');
        } catch (\Exception) {
            $this->confirmDelete = null;
            $this->dispatch('notify', type: 'error', message: 'Não é possível excluir este livro: há movimentações registradas.');
        }
    }

    public function render()
    {
        $user            = auth()->user();
        $materiaFixa     = ($user->isCoordenador() && $user->materia) ? $user->materia : null;

        $livros = Livro::query()
            // Professor só vê os livros da sua matéria
            ->when($materiaFixa, fn ($q) => $q->where('materia', $materiaFixa))
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q) =>
                    $q->where('titulo', 'like', "%{$this->search}%")
                      ->orWhere('isbn', 'like', "%{$this->search}%")
                      ->orWhere('materia', 'like', "%{$this->search}%")
                )
            )
            // Filtro de matéria manual só disponível para almoxarife
            ->when(!$materiaFixa && $this->materia, fn ($q) => $q->where('materia', $this->materia))
            ->orderBy('titulo')
            ->paginate(15);

        // Almoxarife vê filtro por qualquer matéria; professor não precisa (já está fixo)
        $materias = $materiaFixa
            ? collect([$materiaFixa])
            : Livro::select('materia')->distinct()->orderBy('materia')->pluck('materia');

        return view('livewire.livros.index', compact('livros', 'materias'));
    }
}
