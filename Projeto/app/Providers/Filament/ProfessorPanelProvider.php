<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Painel Filament exclusivo para o perfil Coordenador (Professor).
 *
 * Acessível em: /professor
 * Quem acessa: somente usuários com perfil 'coordenador'
 *              (controlado por User::canAccessPanel())
 *
 * Recursos disponíveis:
 *   - Livros: somente visualização (sem criar, editar ou excluir)
 *   - Reservas: criar e acompanhar as próprias reservas
 */
class ProfessorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('professor')
            ->path('professor')
            ->brandName('SenaiStock — Professor')
            ->colors([
                'primary' => Color::Blue, // azul para distinguir do painel do almoxarife
            ])
            // Resources exclusivos do painel professor
            ->discoverResources(
                in: app_path('Filament/Professor/Resources'),
                for: 'App\Filament\Professor\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Professor/Pages'),
                for: 'App\Filament\Professor\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Professor/Widgets'),
                for: 'App\Filament\Professor\Widgets'
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
