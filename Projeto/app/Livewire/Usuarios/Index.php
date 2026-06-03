<?php

namespace App\Livewire\Usuarios;

use App\Enums\PerfilUsuario;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Usuários')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $perfil = '';

    public ?int $confirmDelete = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingPerfil(): void  { $this->resetPage(); }

    public function confirmarExclusao(int $id): void
    {
        // Não pode excluir a si mesmo
        if ($id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'Você não pode excluir o próprio usuário.');
            return;
        }
        $this->confirmDelete = $id;
    }

    public function cancelarExclusao(): void
    {
        $this->confirmDelete = null;
    }

    public function excluir(): void
    {
        $usuario = User::findOrFail($this->confirmDelete);

        // Garante que sempre existe ao menos um almoxarife no sistema
        if ($usuario->isAlmoxarife() && User::where('perfil', PerfilUsuario::Almoxarife)->count() <= 1) {
            $this->confirmDelete = null;
            $this->dispatch('notify', type: 'error', message: 'Não é possível excluir o único almoxarife do sistema.');
            return;
        }

        $usuario->delete();
        $this->confirmDelete = null;
        $this->dispatch('notify', type: 'success', message: 'Usuário excluído com sucesso.');
    }

    public function render()
    {
        $usuarios = User::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when($this->perfil, fn ($q) => $q->where('perfil', $this->perfil))
            ->orderBy('perfil')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.usuarios.index', compact('usuarios'));
    }
}
