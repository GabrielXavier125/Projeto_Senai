<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(string $email, string $senha): array
    {
        $usuario = User::where('email', $email)->first();

        if (! $usuario || ! Hash::check($senha, $usuario->password)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Credenciais inválidas.',
            ];
        }

        $token = $usuario->createToken('api-token')->plainTextToken;

        return [
            'sucesso' => true,
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'nome' => $usuario->name,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil->value,
            ],
        ];
    }

    public function logout(User $usuario): void
    {
        $usuario->currentAccessToken()->delete();
    }
}
