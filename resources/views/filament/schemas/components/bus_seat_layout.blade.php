@php
    $columns = $get('total_columns') ?: 4;
    $rows = $get('total_rows') ?: 5;
    $columnLabel = $get('column_label') ?: 'alpha';
    $rowLabel = $get('row_label') ?: 'numeric';
    $columnLayout = $get('column_layout') ?: '2:2';

    // Generate labels
    $columnLabels = $columnLabel === 'numeric'
        ? range(1, $columns)
        : array_slice(range('A', 'Z'), 0, $columns);
    $rowLabels = $rowLabel === 'numeric'
        ? range(1, $rows)
        : array_slice(range('A', 'Z'), 0, $rows);

    // Parse column layout (e.g., "2:2" -> [2, 2])
    $layoutParts = explode(':', $columnLayout);
    $leftColumns = (int) ($layoutParts[0] ?? 2);
    $rightColumns = (int) ($layoutParts[1] ?? 2);

    // Calculate total configured seats
    $totalConfiguredSeats = $rows * $columns;
@endphp

<div class="text-sm text-gray-600 mb-2">
    <strong>Seat Layout Preview</strong>
    <span class="text-xs">({{ $totalConfiguredSeats }} seats)</span>
</div>

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

        @for ($row = 1; $row <= $rows; $row++)
            <div class="flex gap-2 items-center">
                <!-- Left side seats -->
                @for ($col = 1; $col <= $leftColumns; $col++)
                    @php
                        // Ensure we don't access array indices that don't exist
                        if ($col <= $columns) {
                            $seatNumber = $rowLabel === 'numeric'
                                ? $rowLabels[$row - 1] . $columnLabels[$col - 1]
                                : $columnLabels[$col - 1] . $rowLabels[$row - 1];
                        } else {
                            continue; // Skip this iteration if column doesn't exist
                        }
                    @endphp
                    <div class="w-10 h-10 border-2 border-blue-300 bg-blue-50 rounded flex items-center justify-center text-xs font-medium cursor-pointer hover:bg-blue-100"
                         title="Seat {{ $seatNumber }}">
                        {{ $seatNumber }}
                    </div>
                @endfor

                <!-- Aisle -->
                <div class="w-6 h-10 flex items-center justify-center">
                    <div class="w-1 h-6 bg-gray-300 rounded"></div>
                </div>

                <!-- Right side seats -->
                @for ($col = 1; $col <= $rightColumns; $col++)
                    @php
                        $actualCol = $leftColumns + $col;
                        // Ensure we don't access array indices that don't exist
                        if ($actualCol <= $columns) {
                            $seatNumber = $rowLabel === 'numeric'
                                ? $rowLabels[$row - 1] . $columnLabels[$actualCol - 1]
                                : $columnLabels[$actualCol - 1] . $rowLabels[$row - 1];
                        } else {
                            continue; // Skip this iteration if column doesn't exist
                        }
                    @endphp
                    <div class="w-10 h-10 border-2 border-blue-300 bg-blue-50 rounded flex items-center justify-center text-xs font-medium cursor-pointer hover:bg-blue-100"
                         title="Seat {{ $seatNumber }}">
                        {{ $seatNumber }}
                    </div>
                @endfor
            </div>
        @endfor
    </div>
</x-filament::section>
