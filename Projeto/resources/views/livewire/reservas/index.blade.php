<div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ auth()->user()->isAlmoxarife() ? 'Reservas' : 'Minhas Reservas' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ auth()->user()->isAlmoxarife() ? 'Gerencie todas as reservas de livros' : 'Acompanhe suas reservas' }}
            </p>
        </div>
        @if(auth()->user()->isCoordenador())
            <a href="{{ route('reservas.nova') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nova Reserva
            </a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Filtro status --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex gap-2">
                @foreach(['' => 'Todas', 'pendente' => 'Pendentes', 'retirada' => 'Retiradas', 'cancelada' => 'Canceladas'] as $val => $label)
                <button wire:click="$set('status', '{{ $val }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                            {{ $status === $val ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservado em</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Livro</th>
                        @if(auth()->user()->isAlmoxarife())
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Coordenador</th>
                        @endif
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Qtd.</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Turma / Obs.</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($reservas as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-700">{{ $r->data_reserva->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-400">{{ $r->data_reserva->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-medium text-gray-800 max-w-xs truncate">{{ $r->livro->titulo }}</p>
                            <p class="text-xs text-gray-400">Saldo: {{ $r->livro->saldo_atual }}</p>
                        </td>
                        @if(auth()->user()->isAlmoxarife())
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $r->user->name }}</td>
                        @endif
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $r->livro->temSaldoSuficiente($r->quantidade) ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                {{ $r->quantidade }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @php
                                $statusCores = [
                                    'pendente'  => 'bg-yellow-100 text-yellow-700',
                                    'retirada'  => 'bg-green-100 text-green-700',
                                    'cancelada' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusCores[$r->status->value] }}">
                                {{ $r->status->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate">{{ $r->observacao ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-right">
                            @if($r->isPendente())
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->isAlmoxarife())
                                        <button wire:click="abrirBaixa({{ $r->id }})"
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-600 hover:bg-green-700 text-white transition-colors">
                                            Dar Baixa
                                        </button>
                                    @endif
                                    <button wire:click="abrirCancelar({{ $r->id }})"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border border-red-300 text-red-600 hover:bg-red-50 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            @elseif($r->data_retirada)
                                <span class="text-xs text-gray-400">{{ $r->data_retirada->format('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAlmoxarife() ? 7 : 6 }}" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <p class="font-medium text-sm">Nenhuma reserva encontrada</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservas->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $reservas->links() }}
        </div>
        @endif
    </div>

    {{-- Modal: Dar Baixa --}}
    @if($confirmBaixa && $reservaConfirm)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Confirmar entrega</h3>
            </div>
            <p class="text-sm text-gray-600 mb-2">
                Confirmar a entrega de <strong>{{ $reservaConfirm->quantidade }} exemplar(es)</strong> de:
            </p>
            <p class="text-sm font-semibold text-gray-800 mb-1">{{ $reservaConfirm->livro->titulo }}</p>
            <p class="text-sm text-gray-500 mb-5">Para: {{ $reservaConfirm->user->name }} &mdash; {{ $reservaConfirm->observacao }}</p>
            <p class="text-xs text-gray-400 mb-6">Uma saída de estoque será registrada automaticamente.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="fecharBaixa"
                        class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="darBaixa" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white disabled:opacity-50">
                    <span wire:loading.remove wire:target="darBaixa">Confirmar entrega</span>
                    <span wire:loading wire:target="darBaixa">Processando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Cancelar --}}
    @if($confirmCancelar)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Cancelar reserva?</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">Esta ação não pode ser desfeita.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="fecharCancelar"
                        class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Voltar
                </button>
                <button wire:click="cancelar"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 hover:bg-red-700 text-white">
                    Sim, cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
