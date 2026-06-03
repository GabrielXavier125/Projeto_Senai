<?php

namespace App\Livewire\Reservas;

use App\Enums\StatusReserva;
use App\Models\Livro;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Nova Solicitação')]
class Nova extends Component
{
    /** Seleção: [string(livro_id) => ['selecionado' => bool, 'quantidade' => int]] */
    public array  $selecao    = [];
    public string $observacao = '';

    public function mount(): void
    {
        $user   = auth()->user();
        $livros = Livro::query()
            ->when($user->isCoordenador() && $user->materia, fn ($q) => $q->where('materia', $user->materia))
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'isbn', 'materia', 'saldo_atual']);

        foreach ($livros as $livro) {
            $this->selecao[(string) $livro->id] = [
                'selecionado' => false,
                'quantidade'  => 1,
            ];
        }
    }

    /** Garante que desmarcar redefine a quantidade para 1 */
    public function updatedSelecao(mixed $value, ?string $key): void
    {
        // Livewire também chama este hook quando o array inteiro muda — ignorar
        if (!$key || !str_contains($key, '.')) {
            return;
        }

        [$livroId, $campo] = explode('.', $key, 2);

        if ($campo === 'selecionado' && !(bool) $value) {
            $this->selecao[$livroId]['quantidade'] = 1;
        }
    }

    public function salvar(): void
    {
        $this->validate(
            ['observacao' => ['required', 'string', 'max:500']],
            ['observacao.required' => 'Informe a turma ou motivo da solicitação.']
        );

        $selecionados = collect($this->selecao)
            ->filter(fn ($item) => (bool) ($item['selecionado'] ?? false));

        if ($selecionados->isEmpty()) {
            $this->addError('selecao', 'Selecione pelo menos um livro antes de enviar.');
            return;
        }

        // Valida quantidades
        foreach ($selecionados as $livroId => $item) {
            if ((int) ($item['quantidade'] ?? 0) < 1) {
                $this->addError('selecao', 'Todas as quantidades devem ser pelo menos 1.');
                return;
            }
        }

        // Cria todas as reservas em uma única transação
        DB::transaction(function () use ($selecionados) {
            $agora = now();
            foreach ($selecionados as $livroId => $item) {
                Reserva::create([
                    'livro_id'     => (int) $livroId,
                    'user_id'      => auth()->id(),
                    'quantidade'   => (int) $item['quantidade'],
                    'status'       => StatusReserva::Pendente,
                    'observacao'   => $this->observacao,
                    'data_reserva' => $agora,
                ]);
            }
        });

        $total = $selecionados->count();
        $this->dispatch('notify', type: 'success',
            message: "Solicitação enviada! {$total} reserva(s) criada(s). O almoxarife será notificado.");

        $this->redirect(route('reservas.index'), navigate: true);
    }

    public function render()
    {
        $user   = auth()->user();
        $livros = Livro::query()
            ->when($user->isCoordenador() && $user->materia, fn ($q) => $q->where('materia', $user->materia))
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'isbn', 'materia', 'saldo_atual']);

        return view('livewire.reservas.nova', compact('livros'));
    }
}
