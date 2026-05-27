<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard do painel Almoxarife (/admin).
 *
 * Exibe os widgets de monitoramento de estoque.
 * O acesso é garantido pelo canAccessPanel() do User model —
 * somente almoxarifes chegam até aqui.
 */
class Dashboard extends BaseDashboard
{
    // Sem necessidade de redirect — canAccessPanel() já bloqueia coordenadores
}
