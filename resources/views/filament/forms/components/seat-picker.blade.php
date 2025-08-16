<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $hasUpperDeck = $record->bus?->seat_config?->hasUpperDeck();
        $seatConfiguration = $record->bus->getSeatConfigurationForDate($this->date, $record->id);
        $decks = $hasUpperDeck ? ['lower', 'upper'] : ['lower'];
        $layoutParts = explode(':', $seatConfiguration->lowerDeck->columnLayout);
        $leftColumns = (int) ($layoutParts[0] ?? 2);
        $rightColumns = (int) ($layoutParts[1] ?? 2);
        // dd($seatConfiguration);
    @endphp
    <div
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            activeTab: 'lower'
        }"
        {{ $getExtraAttributeBag() }}
    >

        <div>
            <div class="flex">
                <x-filament::tabs label="Deck Selection">
                    <x-filament::tabs.item
                        alpine-active="activeTab === 'lower'"
                        x-on:click="activeTab = 'lower'"
                    >
                        Lower Deck
                    </x-filament::tabs.item>
                    <x-filament::tabs.item
                        alpine-active="activeTab === 'upper'"
                        x-on:click="activeTab = 'upper'"
                    >
                        Upper Deck
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>
            <div class="flex justify-between">
                <span>{{ __('Select your seats') }}</span>
                <span>{{ __('Max 4 seats') }}</span>
            </div>
            @foreach ($decks as $key => $value)
                @php
                    $deck = $value === 'upper' ? $seatConfiguration->upperDeck : $seatConfiguration->lowerDeck;
                    $seats = $deck->getSeats();
                    $rows = $deck->totalRows;
                    $columns = $deck->totalColumns;
                    $columnOffset = $deck->columnOffset;
                    $rowOffset = $deck->rowOffset;
                @endphp
                <template x-if="activeTab === '{{ $value }}'">
                    <x-filament::section class="mt-4">
                        <div class="flex flex-col items-center gap-2">
                            <!-- Driver area indicator -->
                            <div class="w-full max-w-xs h-8 flex items-center justify-end mr-32 mb-4">
                                <svg class="w-8 h-8" viewBox="0 0 64 64">
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                        <g id="Steering-wheel" sketch:type="MSLayerGroup" transform="translate(1.000000, 1.000000)" stroke="#6B6C6E" stroke-width="2">
                                            <circle id="Oval" sketch:type="MSShapeGroup" cx="31" cy="31" r="31"></circle>
                                            <circle id="Oval" sketch:type="MSShapeGroup" cx="31" cy="31" r="27"></circle>
                                            <path d="M5,38.4 C6.3,38.1 7.6,38 9,38 C18.9,38 27,45.4 27,54.5 C27,55.6 26.9,56.6 26.7,57.6" id="Shape" sketch:type="MSShapeGroup"></path>
                                            <path d="M57.9,28.6 C51,23.9 41.5,21 31,21 C20.5,21 11,23.9 4.1,28.6" id="Shape" sketch:type="MSShapeGroup"></path>
                                            <path d="M35.3,57.6 C35.1,56.6 35,55.5 35,54.5 C35,45.4 43.1,38 53,38 C54.4,38 55.7,38.1 57,38.4" id="Shape" sketch:type="MSShapeGroup"></path>
                                            <circle id="Oval" sketch:type="MSShapeGroup" cx="31" cy="31" r="3"></circle>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            @php
                                $row = 0;
                            @endphp
                            @foreach ($seats as $key => $seat)
                                @if(($row - $rowOffset) !== ($seat->row - $rowOffset))
                                    @php
                                        $row = $seat->row;
                                    @endphp
                                    <div class="flex gap-2 items-center">
                                @endif
                                <div
                                    :class="{
                                        'w-10 h-10 border-2 rounded flex items-center justify-center text-xs font-medium cursor-pointer': true,
                                        'bg-success-100 border-success-300 hover:bg-success-50': state && state.includes(@js($seat->seatNumber)),
                                        'bg-info-100 border-info-300 hover:bg-info-50': @js($seat->isAvailable),
                                        'bg-gray-200 border-gray-300': !@js($seat->isAvailable)
                                    }"
                                    title="Seat {{ $seat->seatNumber }}"
                                    @click="
                                        state.includes(@js($seat->seatNumber))
                                            ? state = state.filter(s => s !== @js($seat->seatNumber))
                                            : (state.length < 4
                                                ? state.push(@js($seat->seatNumber))
                                                : new FilamentNotification()
                                                    .title('Seat Selection Limit Reached')
                                                    .danger()
                                                    .body('You can only select up to 4 seats.')
                                                    .send())"
                                >
                                    {{ $seat->seatNumber }}
                                </div>
                                @if($leftColumns === ($seat->column - $columnOffset))
                                    <!-- Aisle -->
                                    <div class="w-6 h-10 flex items-center justify-center">
                                        <div class="w-1 h-6 bg-gray-300 rounded"></div>
                                    </div>
                                @endif
                                @if($columns === ($seat->column - $columnOffset))
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-filament::section>
                </template>
            @endforeach
        </div>

        <div class="flex gap-2 py-4">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 border bg-success-100 border-success-300"></span>
                <span>Selected</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 border bg-info-100 border-info-300"></span>
                <span>Available</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 border bg-gray-200 border-gray-300"></span>
                <span>Booked</span>
            </div>
            {{-- <x-filament::button
                type="button"
                class="ml-2"
                @click="state = []"
            >
                Clear Selection
            </x-filament::button> --}}
        </div>

</div>
</x-dynamic-component>
