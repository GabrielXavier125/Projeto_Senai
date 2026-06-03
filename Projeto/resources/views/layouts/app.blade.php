<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SenaiStock' }} — SenaiStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-100 antialiased"
      x-data="{
          toasts: [],
          addToast(type, msg) {
              const id = Date.now();
              this.toasts.push({ id, type, msg });
              setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4500);
          }
      }"
      @notify.window="addToast($event.detail.type, $event.detail.message)">

    <div class="flex h-full">

        {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
        <aside class="fixed inset-y-0 left-0 flex flex-col w-64 bg-gray-900 z-30 shadow-xl">

            {{-- Brand --}}
            <div class="flex items-center gap-3 h-16 px-5 bg-gray-950 border-b border-gray-800 shrink-0">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-white tracking-tight">SenaiStock</span>
            </div>

            {{-- Perfil do usuário --}}
            <div class="px-4 py-3 bg-gray-800 border-b border-gray-700 shrink-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium
                    {{ auth()->user()->isAlmoxarife() ? 'bg-amber-500/20 text-amber-300' : 'bg-blue-500/20 text-blue-300' }}">
                    {{ auth()->user()->perfil->label() }}
                </span>
            </div>

            {{-- Navegação --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                @php
                    $link = fn($route, $active) => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors '
                        . (request()->routeIs($active)
                            ? 'bg-indigo-600 text-white'
                            : 'text-gray-300 hover:bg-gray-800 hover:text-white');
                @endphp

                @if(auth()->user()->isAlmoxarife())
                    <a href="{{ route('dashboard') }}" wire:navigate class="{{ $link('dashboard', 'dashboard') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                @endif

                @if(auth()->user()->isAlmoxarife())

                    @php $reservasPendentes = \App\Models\Reserva::where('status', 'pendente')->count(); @endphp

                    <p class="mt-5 mb-1.5 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">Estoque</p>

                    <a href="{{ route('livros.index') }}" wire:navigate class="{{ $link('livros.index', 'livros.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Livros
                    </a>

                    <a href="{{ route('movimentacoes.index') }}" wire:navigate class="{{ $link('movimentacoes.index', 'movimentacoes.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Movimentações
                    </a>

                    <a href="{{ route('reservas.index') }}" wire:navigate class="{{ $link('reservas.index', 'reservas.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span class="flex-1">Reservas</span>
                        @if($reservasPendentes > 0)
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-amber-500 text-white text-xs font-bold">
                                {{ $reservasPendentes > 99 ? '99+' : $reservasPendentes }}
                            </span>
                        @endif
                    </a>

                    <p class="mt-5 mb-1.5 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">Administração</p>

                    <a href="{{ route('usuarios.index') }}" wire:navigate class="{{ $link('usuarios.index', 'usuarios.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Usuários
                    </a>

                @endif

                @if(auth()->user()->isCoordenador())

                    <p class="mt-5 mb-1.5 px-3 text-xs font-semibold uppercase tracking-widest text-gray-500">Catálogo</p>

                    <a href="{{ route('livros.index') }}" wire:navigate class="{{ $link('livros.index', 'livros.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Livros
                    </a>

                    <a href="{{ route('reservas.index') }}" wire:navigate class="{{ $link('reservas.index', 'reservas.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        Minhas Reservas
                    </a>

                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <a href="{{ route('notificacoes.index') }}" wire:navigate class="{{ $link('notificacoes.index', 'notificacoes.*') }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="flex-1">Notificações</span>
                        @if($unread > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        @endif
                    </a>

                @endif

            </nav>

            {{-- Logout --}}
            <div class="px-3 py-4 border-t border-gray-700 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sair do sistema
                    </button>
                </form>
            </div>

        </aside>

        {{-- ── Conteúdo principal ──────────────────────────────────────────── --}}
        <div class="flex flex-col flex-1 min-h-screen ml-64">
            <main class="flex-1 p-8">
                {{ $slot }}
            </main>
            <footer class="px-8 py-3 bg-white border-t border-gray-200">
                <p class="text-xs text-gray-400">SENAI Limeira &mdash; Sistema de Controle de Estoque de Livros Didáticos</p>
            </footer>
        </div>

    </div>

    {{-- ── Toasts ──────────────────────────────────────────────────────────── --}}
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80" aria-live="polite">
        <template x-for="t in toasts" :key="t.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="flex items-start gap-3 p-4 rounded-xl shadow-lg border"
                 :class="{
                     'bg-green-50 border-green-200': t.type==='success',
                     'bg-red-50 border-red-200':     t.type==='error',
                     'bg-yellow-50 border-yellow-200':t.type==='warning',
                     'bg-blue-50 border-blue-200':   t.type==='info',
                 }">
                <p class="text-sm font-medium leading-snug"
                   :class="{
                       'text-green-800': t.type==='success',
                       'text-red-800':   t.type==='error',
                       'text-yellow-800':t.type==='warning',
                       'text-blue-800':  t.type==='info',
                   }"
                   x-text="t.msg"></p>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
