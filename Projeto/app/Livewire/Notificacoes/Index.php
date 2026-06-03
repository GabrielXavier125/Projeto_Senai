<?php

namespace App\Livewire\Notificacoes;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Notificações')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // Marca todas como lidas ao abrir a página
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function excluir(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Notificação removida.');
    }

    public function render()
    {
        $notificacoes = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('livewire.notificacoes.index', compact('notificacoes'));
    }
}
