<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PanelCommonConfig
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
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

        $panel = filament()->getCurrentPanel();
        $panel->brandLogo(asset('logo.svg'))
            ->viteTheme('resources/css/app.css')
            ->sidebarWidth('18rem')
            ->topbar(false);

        return $next($request);
    }
}
