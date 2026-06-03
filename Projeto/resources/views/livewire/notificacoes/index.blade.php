<div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notificações</h1>
            <p class="text-sm text-gray-500 mt-1">Avisos de chegada de livros na sua matéria</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @forelse($notificacoes as $n)
        <div class="flex items-start gap-4 px-6 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors
            {{ is_null($n->read_at) ? 'bg-indigo-50/40' : '' }}">

            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $n->data['mensagem'] }}</p>
                <div class="flex items-center gap-3 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        {{ $n->data['materia'] }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</span>
                    @if(is_null($n->read_at))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Nova</span>
                    @endif
                </div>
            </div>

            <button wire:click="excluir('{{ $n->id }}')" wire:confirm="Remover esta notificação?"
                    class="text-gray-300 hover:text-red-400 transition-colors shrink-0 mt-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @empty
        <div class="px-6 py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="font-medium text-sm">Nenhuma notificação</p>
            <p class="text-xs mt-1">Você será avisado quando chegarem novos livros da sua matéria</p>
        </div>
        @endforelse

        @if($notificacoes->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $notificacoes->links() }}
        </div>
        @endif
    </div>

</div>
