<div class="max-w-4xl">

    {{-- Cabeçalho --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('reservas.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nova Solicitação de Livros</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Selecione os livros e as quantidades desejadas, depois envie uma única solicitação ao almoxarife
                @if(auth()->user()->materia)
                    &mdash; <span class="font-medium text-indigo-600">{{ auth()->user()->materia }}</span>
                @endif
            </p>
        </div>
    </div>

    @php
        $totalSelecionados = collect($selecao)->filter(fn($i) => (bool)($i['selecionado'] ?? false))->count();
    @endphp

    {{-- Lista de livros --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-5">

        {{-- Header da lista --}}
        <div class="flex items-center justify-between px-5 py-3.5 bg-gray-50 border-b border-gray-200">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">
                    {{ $livros->count() }} livro(s) disponível(eis)
                </span>
            </div>
            @if($totalSelecionados > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $totalSelecionados }} selecionado(s)
                </span>
            @endif
        </div>

        @error('selecao')
            <div class="flex items-center gap-2 px-5 py-3 bg-red-50 border-b border-red-200">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-600">{{ $message }}</p>
            </div>
        @enderror

        @if($livros->isEmpty())
            <div class="px-5 py-12 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium">Nenhum livro encontrado para sua matéria</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="w-12 px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Sel.
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Livro
                        </th>
                        <th class="w-32 px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Em Estoque
                        </th>
                        <th class="w-36 px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Qtd. Solicitada
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($livros as $livro)
                        @php
                            $id          = (string) $livro->id;
                            $selecionado = (bool) ($selecao[$id]['selecionado'] ?? false);
                            $quantidade  = $selecao[$id]['quantidade'] ?? 1;
                        @endphp
                        <tr class="transition-colors {{ $selecionado ? 'bg-indigo-50/60' : 'hover:bg-gray-50' }}">

                            {{-- Checkbox --}}
                            <td class="px-4 py-3.5 text-center">
                                <input
                                    type="checkbox"
                                    wire:model.live="selecao.{{ $id }}.selecionado"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            {{-- Informações do livro --}}
                            <td class="px-4 py-3.5">
                                <p class="text-sm font-semibold {{ $selecionado ? 'text-indigo-900' : 'text-gray-800' }}">
                                    {{ $livro->titulo }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-400 font-mono">{{ $livro->isbn }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                        {{ $livro->materia }}
                                    </span>
                                </div>
                            </td>

                            {{-- Saldo em estoque --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($livro->saldo_atual === 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Sem estoque
                                    </span>
                                @elseif($livro->estaBaixoEstoque())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                        {{ $livro->saldo_atual }} un.
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        {{ $livro->saldo_atual }} un.
                                    </span>
                                @endif
                            </td>

                            {{-- Campo de quantidade --}}
                            <td class="px-4 py-3.5 text-center">
                                <input
                                    type="number"
                                    wire:model="selecao.{{ $id }}.quantidade"
                                    min="1"
                                    {{ !$selecionado ? 'disabled' : '' }}
                                    class="w-20 px-2 py-1.5 rounded-lg border text-sm text-center font-medium transition-colors
                                        {{ $selecionado
                                            ? 'border-indigo-300 bg-white text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500'
                                            : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- Rodapé: turma + botão de envio --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Turma / Motivo <span class="text-red-500">*</span>
            </label>
            <textarea
                wire:model="observacao"
                rows="2"
                placeholder="Ex: Turma DS-01 — Desenvolvimento de Sistemas 2026"
                class="w-full px-3 py-2.5 rounded-lg border @error('observacao') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            @error('observacao')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Resumo dos selecionados --}}
        @if($totalSelecionados > 0)
        <div class="mb-5 p-3.5 rounded-lg bg-indigo-50 border border-indigo-200">
            <p class="text-xs font-semibold text-indigo-700 mb-2">Resumo da solicitação:</p>
            <ul class="space-y-1">
                @foreach($livros as $livro)
                    @php $id = (string) $livro->id; @endphp
                    @if($selecao[$id]['selecionado'] ?? false)
                    <li class="flex items-center justify-between text-xs text-indigo-800">
                        <span class="truncate max-w-xs">{{ $livro->titulo }}</span>
                        <span class="ml-3 font-semibold shrink-0">
                            {{ $selecao[$id]['quantidade'] ?? 1 }} exemplar(es)
                        </span>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex items-center justify-between">
            <a href="{{ route('reservas.index') }}" wire:navigate
               class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>

            <button
                wire:click="salvar"
                wire:loading.attr="disabled"
                @if($totalSelecionados === 0) disabled @endif
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors
                    {{ $totalSelecionados > 0
                        ? 'bg-indigo-600 hover:bg-indigo-700 text-white'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}
                    disabled:opacity-60">
                <span wire:loading.remove wire:target="salvar">
                    @if($totalSelecionados > 0)
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Enviar solicitação &mdash; {{ $totalSelecionados }} livro(s)
                    @else
                        Selecione ao menos um livro
                    @endif
                </span>
                <span wire:loading wire:target="salvar">Enviando...</span>
            </button>
        </div>

    </div>

</div>
