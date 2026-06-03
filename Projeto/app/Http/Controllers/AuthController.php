<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $resultado = $this->authService->login(
            $request->email,
            $request->senha
        );

        if (! $resultado['sucesso']) {
            return response()->json(['mensagem' => $resultado['mensagem']], 401);
        }

        return response()->json([
            'token' => $resultado['token'],
            'usuario' => $resultado['usuario'],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['mensagem' => 'Logout realizado com sucesso.'], 200);
    }
}
