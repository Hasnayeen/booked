<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\HomePanelProvider;
use App\Providers\Filament\OperatorPanelProvider;
use App\Providers\PanelServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    AppPanelProvider::class,
    HomePanelProvider::class,
    OperatorPanelProvider::class,
    PanelServiceProvider::class,
];
