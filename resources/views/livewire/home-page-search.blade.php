<div>
    <x-filament::section>
        <form wire:submit="submit">
            {{ $this->form }}
            
            <x-filament::button wire:click="submit" type="submit" class="mt-8 w-full bg-violet-950 text-violet-50 hover:bg-violet-800" size="xl">
                Search
            </x-filament::button>

        </form>
    </x-filament::section>

</div>
