<div>

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Usuários</h1>
            <p class="text-sm text-gray-500 mt-1">Gerencie os acessos ao SenaiStock</p>
        </div>
        <a href="{{ route('usuarios.criar') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Usuário
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Filtros --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nome ou e-mail..."
                       class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <select wire:model.live="perfil"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos os perfis</option>
                <option value="almoxarife">Almoxarife</option>
                <option value="coordenador">Coordenador / Professor</option>
            </select>
        </div>

        {{-- Tabela --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nome</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">E-mail</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Perfil</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Matéria</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($usuarios as $u)
                    <tr class="hover:bg-gray-50 transition-colors {{ $u->id === auth()->id() ? 'bg-indigo-50/30' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shrink-0
                                    {{ $u->isAlmoxarife() ? 'bg-amber-500' : 'bg-blue-500' }}">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $u->name }}
                                        @if($u->id === auth()->id())
                                            <span class="ml-1 text-xs text-indigo-500">(você)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-500">{{ $u->email }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $u->isAlmoxarife() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $u->perfil->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-500">
                            {{ $u->materia ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('usuarios.editar', $u) }}" wire:navigate
                                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Editar
                                </a>
                                @if($u->id !== auth()->id())
                                    <button wire:click="confirmarExclusao({{ $u->id }})"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">
                                        Excluir
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="font-medium text-sm">Nenhum usuário encontrado</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>

    {{-- Modal de confirmação de exclusão --}}
    @if($confirmDelete)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Excluir usuário?</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">
                Esta ação não pode ser desfeita. O histórico de movimentações deste usuário será preservado.
            </p>
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelarExclusao"
                        class="px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button wire:click="excluir" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-red-600 hover:bg-red-700 text-white disabled:opacity-50">
                    <span wire:loading.remove wire:target="excluir">Sim, excluir</span>
                    <span wire:loading wire:target="excluir">Excluindo...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
