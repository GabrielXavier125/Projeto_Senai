<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('livros.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Livro</h1>
            <p class="text-sm text-gray-500 mt-0.5 truncate max-w-sm">{{ $livro->titulo }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="grid grid-cols-1 gap-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Título <span class="text-red-500">*</span></label>
                <input type="text" wire:model="titulo"
                       class="w-full px-3 py-2.5 rounded-lg border @error('titulo') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('titulo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">ISBN <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="isbn"
                           class="w-full px-3 py-2.5 rounded-lg border @error('isbn') border-red-400 @else border-gray-300 @enderror text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('isbn') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Matéria <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="materia"
                           class="w-full px-3 py-2.5 rounded-lg border @error('materia') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('materia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estoque mínimo</label>
                    <input type="number" wire:model="estoque_minimo" min="0"
                           class="w-full px-3 py-2.5 rounded-lg border @error('estoque_minimo') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('estoque_minimo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Saldo atual</label>
                    <div class="flex items-center h-10 px-3 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700">
                        {{ $livro->saldo_atual }} exemplares
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Saldo é alterado apenas por movimentações</p>
                </div>
            </div>

        </div>

        <div class="flex gap-3 justify-end mt-6 pt-5 border-t border-gray-100">
            <a href="{{ route('livros.index') }}" wire:navigate
               class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button wire:click="salvar" wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-lg text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50">
                <span wire:loading.remove wire:target="salvar">Salvar alterações</span>
                <span wire:loading wire:target="salvar">Salvando...</span>
            </button>
        </div>
    </div>

</div>
