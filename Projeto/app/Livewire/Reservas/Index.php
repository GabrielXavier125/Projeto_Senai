<?php

namespace App\Livewire\Reservas;

use App\Enums\StatusReserva;
use App\Models\Reserva;
use App\Services\EstoqueService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reservas')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public ?int $confirmBaixa    = null;
    public ?int $confirmCancelar = null;

    public function updatingStatus(): void { $this->resetPage(); }

    // --- Dar Baixa ---

    public function abrirBaixa(int $id): void
    {
        $this->confirmBaixa = $id;
    }

    public function fecharBaixa(): void
    {
        $this->confirmBaixa = null;
    }

    public function darBaixa(EstoqueService $service): void
    {
        $reserva = Reserva::with('livro')->findOrFail($this->confirmBaixa);

        if (!$reserva->livro->temSaldoSuficiente($reserva->quantidade)) {
            $this->confirmBaixa = null;
            $this->dispatch('notify', type: 'error',
                message: "Estoque insuficiente. Saldo: {$reserva->livro->saldo_atual} | Solicitado: {$reserva->quantidade}.");
            return;
        }

        $obs = "Baixa de reserva #{$reserva->id} — {$reserva->user->name}";
        if ($reserva->observacao) {
            $obs .= " — {$reserva->observacao}";
        }

        $service->registrarSaida(
            livro:      $reserva->livro,
            quantidade: $reserva->quantidade,
            usuario:    auth()->user(),
            observacao: $obs,
        );

        $reserva->update(['status' => StatusReserva::Retirada, 'data_retirada' => now()]);

        $this->confirmBaixa = null;
        $this->dispatch('notify', type: 'success', message: 'Baixa realizada! Saída registrada no estoque.');
    }

    // --- Cancelar ---

    public function abrirCancelar(int $id): void
    {
        $this->confirmCancelar = $id;
    }

    public function fecharCancelar(): void
    {
        $this->confirmCancelar = null;
    }

    public function cancelar(): void
    {
        Reserva::findOrFail($this->confirmCancelar)->update(['status' => StatusReserva::Cancelada]);
        $this->confirmCancelar = null;
        $this->dispatch('notify', type: 'warning', message: 'Reserva cancelada.');
    }

    public function render()
    {
        $query = Reserva::with(['livro:id,titulo,saldo_atual', 'user:id,name'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when(auth()->user()->isCoordenador(), fn ($q) => $q->where('user_id', auth()->id()))
            ->orderByDesc('data_reserva');

        $reservas      = $query->paginate(20);
        $reservaConfirm = $this->confirmBaixa
            ? Reserva::with(['livro:id,titulo,saldo_atual', 'user:id,name'])->find($this->confirmBaixa)
            : null;

        return view('livewire.reservas.index', compact('reservas', 'reservaConfirm'));
    }
}
