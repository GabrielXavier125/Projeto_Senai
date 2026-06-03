<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('usuarios.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Usuário</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $usuario->email }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">

        {{-- Nome --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome completo <span class="text-red-500">*</span></label>
            <input type="text" wire:model="name"
                   class="w-full px-3 py-2.5 rounded-lg border @error('name') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- E-mail --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">E-mail <span class="text-red-500">*</span></label>
            <input type="email" wire:model="email"
                   class="w-full px-3 py-2.5 rounded-lg border @error('email') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Perfil --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Perfil de acesso <span class="text-red-500">*</span></label>

            @if($usuario->id === auth()->id())
                <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        {{ $usuario->perfil->label() }}
                    </span>
                    <p class="text-xs text-gray-500">Não é possível alterar o perfil do próprio usuário.</p>
                </div>
            @else
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                        {{ $perfil === 'almoxarife' ? 'border-amber-400 bg-amber-50' : 'border-gray-300 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="perfil" value="almoxarife" class="text-amber-500 focus:ring-amber-400">
                        <div>
                            <p class="text-sm font-semibold {{ $perfil === 'almoxarife' ? 'text-amber-800' : 'text-gray-700' }}">Almoxarife</p>
                            <p class="text-xs text-gray-400">Gerencia estoque e movimentações</p>
                        </div>
                    </label>
                    <label class="flex-1 flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                        {{ $perfil === 'coordenador' ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="perfil" value="coordenador" class="text-blue-500 focus:ring-blue-400">
                        <div>
                            <p class="text-sm font-semibold {{ $perfil === 'coordenador' ? 'text-blue-800' : 'text-gray-700' }}">Professor / Coordenador</p>
                            <p class="text-xs text-gray-400">Consulta catálogo e faz reservas</p>
                        </div>
                    </label>
                </div>
                @error('perfil') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            @endif
        </div>

        {{-- Matéria (só para coordenador) --}}
        @if($perfil === 'coordenador')
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Matéria que leciona <span class="text-red-500">*</span></label>
            <input type="text" wire:model="materia"
                   list="lista-materias"
                   placeholder="Ex: Programação"
                   class="w-full px-3 py-2.5 rounded-lg border @error('materia') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <datalist id="lista-materias">
                @foreach($materias as $m)
                    <option value="{{ $m }}">
                @endforeach
            </datalist>
            <p class="mt-1 text-xs text-gray-400">O professor verá apenas os livros desta matéria.</p>
            @error('materia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Redefinir senha (opcional) --}}
        <div class="pt-3 border-t border-gray-100">
            <p class="text-sm font-semibold text-gray-700 mb-3">Redefinir senha <span class="text-xs text-gray-400 font-normal">(deixe em branco para manter a atual)</span></p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Nova senha</label>
                    <input type="password" wire:model="password" placeholder="Mínimo 6 caracteres"
                           class="w-full px-3 py-2.5 rounded-lg border @error('password') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirmar nova senha</label>
                    <input type="password" wire:model="password_confirmation" placeholder="Repita a nova senha"
                           class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
            <a href="{{ route('usuarios.index') }}" wire:navigate
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
