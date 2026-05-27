<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller responsável pela autenticação de usuários.
 *
 * Gerencia o fluxo de login e logout via sessão (web).
 * Após login bem-sucedido, redireciona para o painel Filament (/admin).
 */
class LoginController extends Controller
{
    /**
     * Exibe a tela de login.
     *
     * Se o usuário já estiver autenticado, redireciona direto para o painel.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Evita que usuário autenticado veja a tela de login novamente
        if (Auth::check()) {
            return redirect($this->painelDoUsuario());
        }

        return view('auth.login');
    }

    /**
     * Processa o formulário de login.
     *
     * Valida os campos, tenta autenticar via Auth::attempt()
     * e redireciona para o painel em caso de sucesso.
     */
    public function login(Request $request): RedirectResponse
    {
        // Valida os campos obrigatórios do formulário
        $credenciais = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            // Mensagens de erro em português
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        // Tenta autenticar o usuário com as credenciais fornecidas
        // O segundo parâmetro (false) desativa o "lembrar-me"
        if (Auth::attempt($credenciais, false)) {
            // Regenera o ID da sessão para prevenção de session fixation
            $request->session()->regenerate();

            // Redireciona direto para o painel correto do perfil
            // (não usa intended() para evitar redirecionar para o painel errado
            // caso a sessão tenha guardado uma URL de outro perfil)
            return redirect($this->painelDoUsuario());
        }

        // Falha na autenticação: retorna para o login com mensagem de erro
        // Mantém o e-mail digitado no campo (mas nunca a senha)
        return back()
            ->withErrors(['email' => 'E-mail ou senha incorretos.'])
            ->onlyInput('email');
    }

    /**
     * Retorna a URL do painel correto para o usuário autenticado.
     * Almoxarife → /admin | Coordenador → /professor
     */
    private function painelDoUsuario(): string
    {
        return Auth::user()?->isAlmoxarife() ? '/admin' : '/professor';
    }

    /**
     * Encerra a sessão do usuário (logout).
     *
     * Invalida a sessão atual e regenera o token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Desautentica o usuário da sessão atual
        Auth::logout();

        // Invalida a sessão e regenera o token CSRF por segurança
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redireciona para a tela de login
        return redirect()->route('login');
    }
}
