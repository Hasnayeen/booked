<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Bookings\Pages\ListBusBookings;
use App\Filament\Resources\Bookings\Pages\ListHotelBookings;
use App\Http\Middleware\PanelCommonConfig;
use Filament\Events\ServingFilament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Event;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->colors([
                'primary' => Color::Violet,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
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
                PanelCommonConfig::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->strictAuthorization()
            ->databaseNotifications();
    }

    public function boot(): void
    {
        Event::listen(function (ServingFilament $event): void {
            $panel = filament()->getCurrentPanel();
            if ($panel->getId() !== 'app') {
                return;
            }
            $panel->navigationItems([
                NavigationItem::make()
                    ->label('Hotel')
                    ->icon('lucide-hotel')
                    ->group('Bookings')
                    ->isActiveWhen(fn () => request()->fullUrlIs(ListHotelBookings::getUrl()))
                    ->url(ListHotelBookings::getUrl()),
                NavigationItem::make()
                    ->label('Bus')
                    ->icon('lucide-bus')
                    ->group('Bookings')
                    ->isActiveWhen(fn () => request()->fullUrlIs(ListBusBookings::getUrl()))
                    ->url(ListBusBookings::getUrl()),
            ]);
        });
    }
}
