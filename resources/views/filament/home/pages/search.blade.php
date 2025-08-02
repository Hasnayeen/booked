<x-filament-panels::page class="fi-page-search">
    <x-filament::section>
        <form wire:submit="create" class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-10">
                {{ $this->form }}
            </div>

            <div class="flex flex-col justify-end col-span-12 lg:col-span-2">
                <x-filament::button wire:click="search" class="w-full" outlined>
                    Search
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{ $this->content }}
</x-filament-panels::page>
