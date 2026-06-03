<div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Movimentações</h1>
            <p class="text-sm text-gray-500 mt-1">Histórico completo de entradas e saídas</p>
        </div>
        <a href="{{ route('movimentacoes.registrar') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Registrar
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Filtros --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap gap-3 items-end">
            <select wire:model.live="tipo"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos os tipos</option>
                <option value="entrada">Entradas</option>
                <option value="saida">Saídas</option>
            </select>

            <select wire:model.live="livroId"
                    class="flex-1 min-w-40 px-3 py-2 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos os livros</option>
                @foreach($livros as $l)
                    <option value="{{ $l->id }}">{{ $l->titulo }}</option>
                @endforeach
            </select>

            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="dataInicio"
                       class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="text-gray-400 text-sm">até</span>
                <input type="date" wire:model.live="dataFim"
                       class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            @if($tipo || $livroId || $dataInicio || $dataFim)
                <button wire:click="limparFiltros" class="text-sm text-gray-400 hover:text-gray-600 underline">Limpar</button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Data / Hora</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Livro</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Qtd.</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Observação</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($movimentacoes as $mov)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-800">{{ $mov->data_hora->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">{{ $mov->data_hora->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $mov->tipo === \App\Enums\TipoMovimentacao::Entrada ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $mov->tipo->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 max-w-xs truncate">{{ $mov->livro->titulo }}</td>
                        <td class="px-5 py-3.5 text-center text-sm font-semibold text-gray-800">{{ $mov->quantidade }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate">{{ $mov->observacao ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-500">{{ $mov->user->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="font-medium text-sm">Nenhuma movimentação encontrada</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimentacoes->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $movimentacoes->links() }}
        </div>
        @endif
    </div>

</div>
