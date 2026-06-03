<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('movimentacoes.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registrar Movimentação</h1>
            <p class="text-sm text-gray-500 mt-0.5">Registre uma entrada (abastecimento) ou saída (retirada) de livros</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">

        {{-- Combobox de livro: abre dropdown com todos os livros + filtra ao digitar --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Livro <span class="text-red-500">*</span>
            </label>

            <div class="relative"
                 x-data="{
                     open: false,
                     busca: '',
                     livros: @js($livros),
                     get filtrado() {
                         const q = this.busca.trim().toLowerCase();
                         if (!q) return this.livros;
                         return this.livros.filter(l =>
                             l.titulo.toLowerCase().includes(q) ||
                             l.materia.toLowerCase().includes(q)
                         );
                     },
                     selecionar(l) {
                         $wire.livroId = String(l.id);
                         this.busca = l.titulo;
                         this.open  = false;
                     },
                     limpar() {
                         $wire.livroId = '';
                         this.busca = '';
                         this.open  = false;
                     }
                 }"
                 @click.outside="open = false"
                 @keydown.escape="open = false">

                {{-- Input --}}
                <div class="relative">
                    <input
                        type="text"
                        x-model="busca"
                        @focus="open = true"
                        @input="open = true; if (busca === '') limpar()"
                        @keydown.enter.prevent="if (filtrado.length === 1) selecionar(filtrado[0])"
                        placeholder="Selecione ou pesquise pelo título ou matéria..."
                        class="w-full pl-9 pr-9 py-2.5 rounded-lg border @error('livroId') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

                    {{-- Ícone de busca --}}
                    <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                    {{-- Botão limpar (aparece quando há livro selecionado) --}}
                    <button type="button"
                            x-show="busca !== ''"
                            @click="limpar()"
                            class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Dropdown --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute z-30 w-full mt-1 bg-white rounded-xl shadow-xl border border-gray-200 max-h-64 overflow-y-auto">

                    {{-- Lista filtrada --}}
                    <template x-if="filtrado.length > 0">
                        <div>
                            <template x-for="l in filtrado" :key="l.id">
                                <button type="button"
                                        @click="selecionar(l)"
                                        class="flex items-center justify-between w-full px-4 py-2.5 hover:bg-indigo-50 text-left border-b border-gray-50 last:border-0 transition-colors">
                                    <div class="min-w-0 mr-3">
                                        <p class="text-sm font-medium text-gray-800 truncate" x-text="l.titulo"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-text="l.materia"></p>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full"
                                          :class="{
                                              'bg-red-100 text-red-700':    l.saldo === 0,
                                              'bg-yellow-100 text-yellow-700': l.saldo > 0 && l.saldo <= 5,
                                              'bg-green-100 text-green-700':   l.saldo > 5
                                          }"
                                          x-text="'Saldo: ' + l.saldo"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Sem resultados --}}
                    <template x-if="filtrado.length === 0">
                        <div class="px-4 py-4 text-center text-sm text-gray-400">
                            Nenhum livro encontrado para "<span x-text="busca"></span>"
                        </div>
                    </template>
                </div>
            </div>

            @error('livroId')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Badge de saldo (atualizado pelo Livewire após seleção) --}}
            @if($livroId)
                <div class="mt-2">
                    @if($livroSaldo === 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Sem estoque disponível
                        </span>
                    @elseif($livroSaldo <= 5)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                            Estoque baixo — {{ $livroSaldo }} exemplar(es) disponíveis
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Saldo disponível: {{ $livroSaldo }} exemplar(es)
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Tipo --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo <span class="text-red-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex-1 flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                    {{ $tipo === 'entrada' ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="tipo" value="entrada" class="text-green-600 focus:ring-green-500">
                    <div>
                        <p class="text-sm font-semibold {{ $tipo === 'entrada' ? 'text-green-800' : 'text-gray-700' }}">Entrada</p>
                        <p class="text-xs text-gray-400">Abastecimento do estoque</p>
                    </div>
                </label>
                <label class="flex-1 flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                    {{ $tipo === 'saida' ? 'border-red-400 bg-red-50' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="tipo" value="saida" class="text-red-600 focus:ring-red-500">
                    <div>
                        <p class="text-sm font-semibold {{ $tipo === 'saida' ? 'text-red-800' : 'text-gray-700' }}">Saída</p>
                        <p class="text-xs text-gray-400">Retirada para turma</p>
                    </div>
                </label>
            </div>
            @error('tipo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Quantidade --}}
        <div class="w-48">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Quantidade <span class="text-red-500">*</span></label>
            <input type="number" wire:model="quantidade" min="1"
                   class="w-full px-3 py-2.5 rounded-lg border @error('quantidade') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @if($livroId && $tipo === 'saida' && $livroSaldo > 0)
                <p class="mt-1 text-xs text-gray-400">Máximo disponível: {{ $livroSaldo }} exemplares</p>
            @endif
            @error('quantidade') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Observação --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Observação @if($tipo === 'saida') <span class="text-red-500">*</span> @endif
            </label>
            <textarea wire:model="observacao" rows="3"
                      placeholder="{{ $tipo === 'saida' ? 'Ex: Turma DS-01 — Desenvolvimento de Sistemas 2026' : 'Ex: NF 4521 — Editora Érica' }}"
                      class="w-full px-3 py-2.5 rounded-lg border @error('observacao') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            @if($tipo === 'saida')
                <p class="mt-1 text-xs text-gray-400">Obrigatório para saídas — informe a turma ou o motivo</p>
            @endif
            @error('observacao') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
            <a href="{{ route('movimentacoes.index') }}" wire:navigate
               class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button wire:click="registrar" wire:loading.attr="disabled"
                    class="px-5 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50 transition-colors
                        {{ $tipo === 'saida' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                <span wire:loading.remove wire:target="registrar">
                    Registrar {{ $tipo === 'saida' ? 'Saída' : 'Entrada' }}
                </span>
                <span wire:loading wire:target="registrar">Registrando...</span>
            </button>
        </div>

    </div>

</div>
