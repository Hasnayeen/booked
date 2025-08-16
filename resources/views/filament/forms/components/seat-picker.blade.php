<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $isUpperDeck = $getDeck() === 'upper';
        $seatConfiguration = $record->bus->getSeatConfigurationForDate($this->date, $record->id);
        if ($isUpperDeck) {
            $upperDeck = $seatConfiguration->upperDeck;
            $seats = $upperDeck->getSeats();
        } else {
            $lowerDeck = $seatConfiguration->lowerDeck;
            $seats = $lowerDeck->getSeats();
        }
        // dd($seatConfiguration);
        if ($record->bus?->seat_config?->hasUpperDeck() && $isUpperDeck) {
            $rows = $upperDeck->totalRows;
            $columns = $upperDeck->totalColumns;
            $columnLayout = $upperDeck->columnLayout;
            $columnOffset = $upperDeck->columnOffset;
            $rowOffset = $upperDeck->rowOffset;
        } else {
            $rows = $lowerDeck->totalRows;
            $columns = $lowerDeck->totalColumns;
            $columnLayout = $lowerDeck->columnLayout;
            $columnOffset = $lowerDeck->columnOffset;
            $rowOffset = $lowerDeck->rowOffset;
        }
    @endphp
    <div
        x-data="{ state: $wire.$entangle(@js($getStatePath())) }"
        {{ $getExtraAttributeBag() }}
    >

        @php
            // Parse column layout (e.g., "2:2" -> [2, 2])
            $layoutParts = explode(':', $columnLayout);
            $leftColumns = (int) ($layoutParts[0] ?? 2);
            $rightColumns = (int) ($layoutParts[1] ?? 2);
        @endphp

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
                            'bg-success-100 border-success-300 hover:bg-success-50': state.includes(@js($seat->seatNumber)),
                            'bg-info-100 border-info-300 hover:bg-info-50': @js($seat->isAvailable),
                            'bg-gray-200 border-gray-300': !@js($seat->isAvailable)
                        }"
                        title="Seat {{ $seat->seatNumber }}"
                        @click="state.includes(@js($seat->seatNumber)) ? state = state.filter(s => s !== @js($seat->seatNumber)) : state.push(@js($seat->seatNumber))"
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
