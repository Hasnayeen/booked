<div class="fixed w-16 left-0 bottom-0 z-20 fi-btm-menu-ctn">
    <div class="flex flex-col justify-center items-center space-y-4 py-8 h-full">
            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    <div class="bg-gray-200 dark:bg-gray-800 p-2.5 rounded-full">
                        @livewire(Filament\Livewire\DatabaseNotifications::class, [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                        ])
                    </div>
                @endif
                @if (filament()->hasUserMenu())
                    @livewire(\Filament\Livewire\SimpleUserMenu::class)
                @endif
            @endif
    </div>
</div>
