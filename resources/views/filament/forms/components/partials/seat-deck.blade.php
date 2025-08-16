@php
    use App\ValueObjects\SeatPosition;
    
    $seats = $deck->getSeats();
    $totalRows = $deck->totalRows;
    $totalColumns = $deck->totalColumns;
    $columnLayout = explode(':', $deck->columnLayout);
    $leftColumns = (int)$columnLayout[0];
    $rightColumns = (int)$columnLayout[1];
@endphp

<div class="seat-deck">
    {{-- Column Headers --}}
    <div class="grid gap-1 mb-2" style="grid-template-columns: 2rem repeat({{ $leftColumns }}, 2.5rem) 2rem repeat({{ $rightColumns }}, 2.5rem);">
        <div></div> {{-- Row label space --}}
        @for($col = 1; $col <= $leftColumns; $col++)
            <div class="text-center text-xs text-gray-500 font-medium">
                {{ $deck->getColumnLabel($col) }}
            </div>
        @endfor
        <div></div> {{-- Aisle space --}}
        @for($col = $leftColumns + 1; $col <= $totalColumns; $col++)
            <div class="text-center text-xs text-gray-500 font-medium">
                {{ $deck->getColumnLabel($col) }}
            </div>
        @endfor
    </div>

    {{-- Seat Rows --}}
    @for($row = 1; $row <= $totalRows; $row++)
        <div class="grid gap-1 mb-1" style="grid-template-columns: 2rem repeat({{ $leftColumns }}, 2.5rem) 2rem repeat({{ $rightColumns }}, 2.5rem);">
            {{-- Row Label --}}
            <div class="flex items-center justify-center text-xs text-gray-500 font-medium">
                {{ $deck->getRowLabel($row) }}
            </div>

            {{-- Left Side Seats --}}
            @for($col = 1; $col <= $leftColumns; $col++)
                @php
                    $seat = $seats->first(fn(SeatPosition $s) => $s->row === $row && $s->column === $col);
                @endphp
                @if($seat)
                    <button
                        type="button"
                        @click="toggleSeat('{{ $seat->seatNumber }}', {{ $seat->isAvailable ? 'true' : 'false' }})"
                        :class="getSeatClass({{ json_encode($seat->toArray()) }})"
                        class="w-10 h-8 rounded text-xs font-medium transition-colors duration-200 flex items-center justify-center"
                        :disabled="!{{ $seat->isAvailable ? 'true' : 'false' }}"
                        title="Seat {{ $seat->seatNumber }}{{ $seat->isAvailable ? '' : ' - Booked' }}"
                    >
                        {{ $seat->seatNumber }}
                    </button>
                @else
                    <div class="w-10 h-8"></div> {{-- Empty space --}}
                @endif
            @endfor

            {{-- Aisle --}}
            <div class="w-8 flex items-center justify-center">
                <div class="w-px h-6 bg-gray-300"></div>
            </div>

            {{-- Right Side Seats --}}
            @for($col = $leftColumns + 1; $col <= $totalColumns; $col++)
                @php
                    $seat = $seats->first(fn(SeatPosition $s) => $s->row === $row && $s->column === $col);
                @endphp
                @if($seat)
                    <button
                        type="button"
                        @click="toggleSeat('{{ $seat->seatNumber }}', {{ $seat->isAvailable ? 'true' : 'false' }})"
                        :class="getSeatClass({{ json_encode($seat->toArray()) }})"
                        class="w-10 h-8 rounded text-xs font-medium transition-colors duration-200 flex items-center justify-center"
                        :disabled="!{{ $seat->isAvailable ? 'true' : 'false' }}"
                        title="Seat {{ $seat->seatNumber }}{{ $seat->isAvailable ? '' : ' - Booked' }}"
                    >
                        {{ $seat->seatNumber }}
                    </button>
                @else
                    <div class="w-10 h-8"></div> {{-- Empty space --}}
                @endif
            @endfor
        </div>
    @endfor
</div>
