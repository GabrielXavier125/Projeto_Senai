<?php

namespace App\Livewire\Movimentacoes;

use App\Enums\TipoMovimentacao;
use App\Models\Livro;
use App\Models\Movimentacao;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Movimentações')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $tipo = '';

    #[Url]
    public string $livroId = '';

    #[Url]
    public string $dataInicio = '';

    #[Url]
    public string $dataFim = '';

    public function updatingTipo(): void       { $this->resetPage(); }
    public function updatingLivroId(): void    { $this->resetPage(); }
    public function updatingDataInicio(): void { $this->resetPage(); }
    public function updatingDataFim(): void    { $this->resetPage(); }

    public function limparFiltros(): void
    {
        $this->tipo = $this->livroId = $this->dataInicio = $this->dataFim = '';
        $this->resetPage();
    }

    public function render()
    {
        $movimentacoes = Movimentacao::with(['livro:id,titulo', 'user:id,name'])
            ->when($this->tipo,       fn ($q) => $q->where('tipo', $this->tipo))
            ->when($this->livroId,    fn ($q) => $q->where('livro_id', $this->livroId))
            ->when($this->dataInicio, fn ($q) => $q->whereDate('data_hora', '>=', $this->dataInicio))
            ->when($this->dataFim,    fn ($q) => $q->whereDate('data_hora', '<=', $this->dataFim))
            ->orderByDesc('data_hora')
            ->paginate(20);

        $livros = Livro::select(['id', 'titulo'])->orderBy('titulo')->get();

        return view('livewire.movimentacoes.index', compact('movimentacoes', 'livros'));
    }
}
