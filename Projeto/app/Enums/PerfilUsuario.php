<?php

namespace App\Enums;

/**
 * Enum que representa os perfis de usuário do sistema.
 *
 * Cada perfil define o nível de acesso e as ações permitidas:
 *   - Almoxarife: operações de estoque (entradas, saídas, cadastro de livros)
 *   - Coordenador: consultas, relatórios e monitoramento de estoque
 *
 * Usando PHP Enum (disponível a partir do PHP 8.1).
 * O tipo "string" garante que o valor salvo no banco seja o texto ('almoxarife', 'coordenador').
 */
enum PerfilUsuario: string
{
    /** Perfil operacional — registra entradas e saídas de livros */
    case Almoxarife = 'almoxarife';

    /** Perfil de gestão — consulta relatórios e monitora o estoque */
    case Coordenador = 'coordenador';

    /**
     * Retorna o nome legível do perfil para exibição na interface.
     */
    public function label(): string
    {
        return match($this) {
            self::Almoxarife => 'Almoxarife',
            self::Coordenador => 'Coordenador',
        };
    }

    /**
     * Retorna a cor de destaque do perfil (usada no painel Filament).
     * Amber para almoxarife, azul para coordenador.
     */
    public function cor(): string
    {
        return match($this) {
            self::Almoxarife => 'warning',
            self::Coordenador => 'info',
        };
    }
}
