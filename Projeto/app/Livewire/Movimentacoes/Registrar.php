<?php

namespace App\Livewire\Movimentacoes;

use App\Enums\PerfilUsuario;
use App\Models\Livro;
use App\Models\User;
use App\Notifications\NovaEntradaLivro;
use App\Services\EstoqueService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Registrar Movimentação')]
class Registrar extends Component
{
    public string $livroId   = '';
    public int    $livroSaldo = 0;
    public string $tipo       = '';
    public int    $quantidade  = 1;
    public string $observacao  = '';

    /** Atualiza o saldo exibido quando o almoxarife troca o livro selecionado */
    public function updatedLivroId(): void
    {
        $this->livroSaldo = $this->livroId
            ? (int) Livro::where('id', $this->livroId)->value('saldo_atual')
            : 0;
    }

    protected function rules(): array
    {
        return [
            'livroId'    => ['required', 'exists:livros,id'],
            'tipo'       => ['required', 'in:entrada,saida'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacao' => ['required_if:tipo,saida', 'nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'livroId.required'       => 'Selecione um livro.',
            'tipo.required'          => 'Selecione o tipo de movimentação.',
            'quantidade.required'    => 'Informe a quantidade.',
            'quantidade.min'         => 'A quantidade deve ser maior que zero.',
            'observacao.required_if' => 'A observação é obrigatória para saídas.',
        ];
    }

    public function registrar(EstoqueService $service): void
    {
        $this->validate();

        $livro = Livro::findOrFail($this->livroId);

        try {
            if ($this->tipo === 'entrada') {
                $service->registrarEntrada(
                    livro:      $livro,
                    quantidade: $this->quantidade,
                    usuario:    auth()->user(),
                    observacao: $this->observacao,
                );

                $professores = User::where('perfil', PerfilUsuario::Coordenador)
                    ->where('materia', $livro->materia)
                    ->get();

                foreach ($professores as $professor) {
                    $professor->notify(new NovaEntradaLivro($livro, $this->quantidade));
                }

                $notificados = $professores->count();
                $extra = $notificados > 0
                    ? " {$notificados} professor(es) de {$livro->materia} notificado(s)."
                    : '';

                $this->dispatch('notify', type: 'success', message: "Entrada registrada com sucesso!{$extra}");
            } else {
                $service->registrarSaida(
                    livro:      $livro,
                    quantidade: $this->quantidade,
                    usuario:    auth()->user(),
                    observacao: $this->observacao,
                );

                $this->dispatch('notify', type: 'success', message: 'Saída registrada com sucesso!');
            }

            $this->redirect(route('movimentacoes.index'), navigate: true);

        } catch (\DomainException $e) {
            $this->addError('quantidade', $e->getMessage());
        }
    }

    public function render()
    {
        // Array plano para o Alpine.js filtrar no browser (sem roundtrip ao digitar)
        $livros = Livro::orderBy('materia')
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'materia', 'saldo_atual'])
            ->map(fn ($l) => [
                'id'     => $l->id,
                'titulo' => $l->titulo,
                'materia'=> $l->materia,
                'saldo'  => $l->saldo_atual,
            ])
            ->values();

        return view('livewire.movimentacoes.registrar', compact('livros'));
    }
}
