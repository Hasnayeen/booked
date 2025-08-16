@php
    use App\ValueObjects\SeatPosition;
    
    $seatConfiguration = $getSeatConfiguration();
    $isDisabled = $isDisabled();
    $selectedSeats = $getState() ?? [];
    $passengerCount = $getPassengerCount() ?? 1;
    $allowMultipleSelection = $getAllowMultipleSelection();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @if($seatConfiguration)
        <div
            x-data="{
                selectedSeats: @js($selectedSeats),
                maxSeats: @js($passengerCount),
                allowMultiple: @js($allowMultipleSelection),
                
                toggleSeat(seatNumber, isAvailable) {
                    if (!isAvailable) return;
                    
                    const index = this.selectedSeats.indexOf(seatNumber);
                    
                    if (index > -1) {
                        // Deselect seat
                        this.selectedSeats.splice(index, 1);
                    } else {
                        // Select seat
                        if (this.allowMultiple) {
                            if (this.selectedSeats.length < this.maxSeats) {
                                this.selectedSeats.push(seatNumber);
                            }
                        } else {
                            this.selectedSeats = [seatNumber];
                        }
                    }
                    
                    $wire.set('{{ $getStatePath() }}', this.selectedSeats);
                },
                
                isSeatSelected(seatNumber) {
                    return this.selectedSeats.includes(seatNumber);
                },
                
                getSeatClass(seat) {
                    if (!seat.is_available) {
                        return 'bg-red-500 text-white cursor-not-allowed';
                    }
                    
                    if (this.isSeatSelected(seat.seat_number)) {
                        return 'bg-green-500 text-white';
                    }
                    
                    return 'bg-gray-200 hover:bg-gray-300 cursor-pointer';
                }
            }"
            class="w-full"
        >
            {{-- Selection Info --}}
            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">{{ __('seat_layout.selected_seats') }}: <span x-text="selectedSeats.length"></span> / {{ $passengerCount }}</span>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-gray-200 rounded"></div>
                            <span>{{ __('seat_layout.available') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-green-500 rounded"></div>
                            <span>{{ __('seat_layout.selected') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-red-500 rounded"></div>
                            <span>{{ __('seat_layout.booked') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bus Layout --}}
            <div class="border rounded-lg p-4 bg-white">
                {{-- Driver Section --}}
                <div class="flex justify-between items-center mb-6 pb-4 border-b">
                    <div class="text-sm text-gray-500">{{ __('seat_layout.driver') }}</div>
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-sm text-gray-500">{{ __('seat_layout.door') }}</div>
                </div>

                {{-- Lower Deck --}}
                @if($seatConfiguration->lowerDeck)
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold mb-3 text-gray-700">
                            @if($seatConfiguration->isDoubleDeck())
                                {{ __('seat_layout.lower_deck') }}
                            @else
                                {{ __('seat_layout.seats') }}
                            @endif
                        </h4>
                        @include('filament.forms.components.partials.seat-deck', [
                            'deck' => $seatConfiguration->lowerDeck,
                            'deckType' => 'lower'
                        ])
                    </div>
                @endif

                {{-- Upper Deck --}}
                @if($seatConfiguration->upperDeck)
                    <div>
                        <h4 class="text-sm font-semibold mb-3 text-gray-700">{{ __('seat_layout.upper_deck') }}</h4>
                        @include('filament.forms.components.partials.seat-deck', [
                            'deck' => $seatConfiguration->upperDeck,
                            'deckType' => 'upper'
                        ])
                    </div>
                @endif
            </div>

            {{-- Selected Seats Summary --}}
            <div x-show="selectedSeats.length > 0" class="mt-4 p-3 bg-green-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-green-800">{{ __('seat_layout.selected_seats') }}:</span>
                    <span x-text="selectedSeats.join(', ')" class="text-green-700"></span>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 text-center text-gray-500">
            <p>{{ __('seat_layout.no_route_selected') }}</p>
        </div>
    @endif
</x-dynamic-component>