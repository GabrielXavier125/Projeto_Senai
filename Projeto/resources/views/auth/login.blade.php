<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Título da aba do navegador --}}
    <title>Login — SenaiStock</title>

    <style>
        /* =============================================
           Reset e configuração base
           ============================================= */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* =============================================
           Card de login
           ============================================= */
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.10);
            padding: 40px 36px;
            width: 100%;
            max-width: 400px;
        }

        /* =============================================
           Cabeçalho — logo e título
           ============================================= */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header .logo-icon {
            /* Ícone simples representando um livro */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: #f59e0b; /* Amber — cor padrão do Filament */
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .login-header .logo-icon svg {
            width: 32px;
            height: 32px;
            fill: #ffffff;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: -0.5px;
        }

        .login-header p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 4px;
        }

        /* =============================================
           Mensagem de erro de autenticação
           ============================================= */
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.875rem;
            margin-bottom: 20px;
        }

        /* =============================================
           Campos do formulário
           ============================================= */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
            color: #1f2937;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        /* Estado de foco no campo — destaque em amber */
        .form-group input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        /* Borda vermelha quando há erro de validação */
        .form-group input.is-invalid {
            border-color: #ef4444;
        }

        /* Texto de erro abaixo do campo */
        .field-error {
            font-size: 0.8rem;
            color: #ef4444;
            margin-top: 4px;
        }

        /* =============================================
           Botão de submit
           ============================================= */
        .btn-login {
            width: 100%;
            background-color: #f59e0b;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 8px;
        }

        .btn-login:hover {
            background-color: #d97706;
        }

        .btn-login:active {
            background-color: #b45309;
        }

        /* =============================================
           Rodapé do card
           ============================================= */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    <div class="login-card">

        {{-- Cabeçalho com ícone e nome do sistema --}}
        <div class="login-header">
            <div class="logo-icon">
                {{-- Ícone de livro (SVG inline) --}}
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10
                             10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4
                             0h-2V8h2v8z"/>
                </svg>
            </div>
            <h1>SenaiStock</h1>
            <p>Controle de Estoque de Livros Didáticos</p>
        </div>

        {{-- Exibe mensagem de erro geral (e-mail ou senha incorretos) --}}
        @if ($errors->has('email'))
            <div class="alert-error">
                {{ $errors->first('email') }}
            </div>
        @endif

        {{-- Formulário de login --}}
        {{-- action aponta para a rota POST /login definida em web.php --}}
        <form method="POST" action="{{ route('login') }}">

            {{-- Token CSRF: protege contra ataques de falsificação de requisição --}}
            @csrf

            {{-- Campo de e-mail --}}
            <div class="form-group">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="seu@email.com"
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    required
                    autofocus
                >
            </div>

            {{-- Campo de senha --}}
            <div class="form-group">
                <label for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            {{-- Botão de envio --}}
            <button type="submit" class="btn-login">
                Entrar
            </button>

        </form>

        {{-- Rodapé informativo --}}
        <div class="login-footer">
            SENAI Limeira &mdash; Sistema Interno
        </div>

    </div>

</body>
</html>
