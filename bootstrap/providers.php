<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\GuestPanelProvider;
use App\Providers\Filament\OperatorPanelProvider;
use App\Providers\PanelServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    AppPanelProvider::class,
    GuestPanelProvider::class,
    OperatorPanelProvider::class,
    PanelServiceProvider::class,
];
