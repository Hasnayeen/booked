<?php

namespace App\Providers\Filament;

use App\Filament\Operator\Pages\Tenancy\EditOperatorProfile;
use App\Filament\Operator\Pages\Tenancy\RegisterOperator;
use App\Models\Operator;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OperatorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('operator')
            ->path('operator')
            ->login()
            ->registration()
            ->emailVerification()
            ->tenant(Operator::class)
            ->tenantRegistration(RegisterOperator::class)
            ->tenantProfile(EditOperatorProfile::class)
            ->brandLogo(asset('logo.svg'))
            ->colors([
                'primary' => Color::Cyan,
            ])
            ->viteTheme('resources/css/theme.css')
            ->sidebarWidth('18rem')
            ->discoverResources(in: app_path('Filament/Operator/Resources'), for: 'App\Filament\Operator\Resources')
            ->discoverPages(in: app_path('Filament/Operator/Pages'), for: 'App\Filament\Operator\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Operator/Widgets'), for: 'App\Filament\Operator\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->strictAuthorization()
            ->databaseNotifications();
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn () => view('filament.operator.hooks.tenant-sidebar'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn () => view('filament.operator.hooks.global-search'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn () => view('filament.operator.hooks.user-menu'),
        );
    }
}
