<div>

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Livros</h1>
            <p class="text-sm text-gray-500 mt-1">Catálogo de livros didáticos do SENAI</p>
        </div>
        @if(auth()->user()->isAlmoxarife())
            <a href="{{ route('livros.criar') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Livro
            </a>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="{{ auth()->user()->isCoordenador() ? 'Buscar por título ou ISBN...' : 'Buscar por título, ISBN ou matéria...' }}"
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            @if(auth()->user()->isAlmoxarife())
            <select wire:model.live="materia"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">Todas as matérias</option>
                @foreach($materias as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
            @endif
        </div>

        {{-- Tabela --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Título</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ISBN</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Matéria</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Saldo</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Mínimo</th>
                        @if(auth()->user()->isAlmoxarife())
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($livros as $livro)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5 text-sm font-medium text-gray-900 max-w-xs truncate">{{ $livro->titulo }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 font-mono">{{ $livro->isbn }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $livro->materia }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $livro->estaBaixoEstoque() ? ($livro->saldo_atual === 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') : 'bg-green-100 text-green-700' }}">
                                {{ $livro->saldo_atual }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-sm text-gray-500">{{ $livro->estoque_minimo }}</td>
                        @if(auth()->user()->isAlmoxarife())
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('livros.editar', $livro) }}" wire:navigate
                                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                <button wire:click="confirmarExclusao({{ $livro->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Excluir</button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAlmoxarife() ? 6 : 5 }}" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-medium text-sm">Nenhum livro encontrado</p>
                            <p class="text-xs mt-1">Tente ajustar os filtros</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($livros->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $livros->links() }}
        </div>
        @endif
    </div>

    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDelete)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Excluir livro?</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">Esta ação não pode ser desfeita. Livros com movimentações registradas não podem ser excluídos.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelarExclusao"
                        class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="excluir"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 hover:bg-red-700 text-white">
                    Sim, excluir
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
