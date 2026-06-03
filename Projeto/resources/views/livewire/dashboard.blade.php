<div wire:poll.60s>

    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Visão geral do estoque · atualiza a cada 60s</p>
    </div>

    {{-- Cards de estatísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Títulos Cadastrados</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalLivros }}</p>
            <p class="mt-1 text-xs text-gray-400">livros no catálogo</p>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5 {{ $baixoEstoque > 0 ? 'border-red-200' : 'border-gray-200' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Baixo Estoque</p>
            <p class="mt-2 text-3xl font-bold {{ $baixoEstoque > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $baixoEstoque }}</p>
            <p class="mt-1 text-xs {{ $baixoEstoque > 0 ? 'text-red-400' : 'text-gray-400' }}">
                {{ $baixoEstoque > 0 ? 'requer atenção' : 'estoque saudável' }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Entradas Hoje</p>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ $entradasHoje }}</p>
            <p class="mt-1 text-xs text-gray-400">abastecimentos</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Saídas Hoje</p>
            <p class="mt-2 text-3xl font-bold text-amber-600">{{ $saidasHoje }}</p>
            <p class="mt-1 text-xs text-gray-400">retiradas registradas</p>
        </div>

    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Livros com baixo estoque --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">Baixo Estoque</h2>
                @if(auth()->user()->isAlmoxarife())
                    <a href="{{ route('livros.index') }}" wire:navigate class="text-xs text-indigo-600 hover:underline">Ver todos</a>
                @endif
            </div>
            @if($livrosBaixo->isEmpty())
                <p class="px-5 py-8 text-sm text-center text-gray-400">Todos os livros estão com estoque adequado ✓</p>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Título</th>
                            <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Saldo</th>
                            <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Mín.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($livrosBaixo as $l)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-800 max-w-xs truncate">{{ $l->titulo }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $l->saldo_atual === 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $l->saldo_atual }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-500 text-xs">{{ $l->estoque_minimo }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Movimentações recentes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">Movimentações Recentes</h2>
                @if(auth()->user()->isAlmoxarife())
                    <a href="{{ route('movimentacoes.index') }}" wire:navigate class="text-xs text-indigo-600 hover:underline">Ver histórico</a>
                @endif
            </div>
            @if($movRecentes->isEmpty())
                <p class="px-5 py-8 text-sm text-center text-gray-400">Nenhuma movimentação registrada ainda.</p>
            @else
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Livro</th>
                            <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                            <th class="px-5 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Qtd</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Quando</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($movRecentes as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-800 max-w-xs truncate">{{ $m->livro->titulo }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $m->tipo === \App\Enums\TipoMovimentacao::Entrada ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $m->tipo->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ $m->quantidade }}</td>
                            <td class="px-5 py-3 text-gray-400 text-xs">{{ $m->data_hora->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Reservas pendentes (só almoxarife) --}}
        @if(auth()->user()->isAlmoxarife() && $reservasPendentes && $reservasPendentes->isNotEmpty())
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden xl:col-span-2">
            <div class="px-5 py-4 border-b border-amber-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-amber-800">⚠ Reservas Pendentes</h2>
                <a href="{{ route('reservas.index') }}" wire:navigate class="text-xs text-indigo-600 hover:underline">Ver todas</a>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-amber-600 uppercase tracking-wide">Livro</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-amber-600 uppercase tracking-wide">Coordenador</th>
                        <th class="px-5 py-2.5 text-center text-xs font-semibold text-amber-600 uppercase tracking-wide">Qtd</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-amber-600 uppercase tracking-wide">Turma</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-50">
                    @foreach($reservasPendentes as $r)
                    <tr class="hover:bg-amber-50">
                        <td class="px-5 py-3 text-gray-800">{{ $r->livro->titulo }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $r->user->name }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $r->livro->temSaldoSuficiente($r->quantidade) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $r->quantidade }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $r->observacao }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

</div>
