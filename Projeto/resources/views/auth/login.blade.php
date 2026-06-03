<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SenaiStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-100 flex items-center justify-center p-4">

<div class="w-full max-w-sm">

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">SenaiStock</h1>
            <p class="text-sm text-gray-500 mt-1">Controle de Estoque de Livros Didáticos</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-6">

            @if ($errors->any())
            <div class="flex items-center gap-3 p-3 mb-5 rounded-lg bg-red-50 border border-red-200">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700">{{ $errors->first('email') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">E-mail</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="seu@email.com"
                           autocomplete="email"
                           autofocus
                           class="w-full px-3 py-2.5 rounded-lg border @error('email') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Senha</label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password"
                           class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition-colors mt-2">
                    Entrar
                </button>
            </form>
        </div>

    </div>

    <p class="text-center text-xs text-gray-400 mt-6">SENAI Limeira &mdash; Sistema Interno</p>

</div>

</body>
</html>
