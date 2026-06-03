<?php

namespace App\Livewire;

use App\Enums\TipoMovimentacao;
use App\Models\Livro;
use App\Models\Movimentacao;
use App\Models\Reserva;
use App\Enums\StatusReserva;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $totalLivros  = Livro::count();
        $baixoEstoque = Livro::whereColumn('saldo_atual', '<=', 'estoque_minimo')->count();
        $entradasHoje = Movimentacao::where('tipo', TipoMovimentacao::Entrada)->whereDate('data_hora', today())->count();
        $saidasHoje   = Movimentacao::where('tipo', TipoMovimentacao::Saida)->whereDate('data_hora', today())->count();

        $livrosBaixo = Livro::select(['id', 'titulo', 'materia', 'saldo_atual', 'estoque_minimo'])
            ->whereColumn('saldo_atual', '<=', 'estoque_minimo')
            ->orderBy('saldo_atual')
            ->limit(10)
            ->get();

        $movRecentes = Movimentacao::with(['livro:id,titulo', 'user:id,name'])
            ->select(['id', 'livro_id', 'user_id', 'tipo', 'quantidade', 'data_hora'])
            ->latest('data_hora')
            ->limit(6)
            ->get();

        $reservasPendentes = null;
        if (auth()->user()->isAlmoxarife()) {
            $reservasPendentes = Reserva::with(['livro:id,titulo,saldo_atual', 'user:id,name'])
                ->where('status', StatusReserva::Pendente)
                ->latest('data_reserva')
                ->limit(5)
                ->get();
        }

        return view('livewire.dashboard', compact(
            'totalLivros', 'baixoEstoque', 'entradasHoje', 'saidasHoje',
            'livrosBaixo', 'movRecentes', 'reservasPendentes'
        ));
    }
}
