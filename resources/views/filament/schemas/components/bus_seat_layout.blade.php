@php
    $columns = $get('total_columns');
    $rows = $get('total_rows');
    $alphabets = range('A', 'Z');
    $numbers = range(1, $get('total_rows'));
@endphp

<x-filament::section class="mt-4">
    <div class="flex flex-col items-center gap-2">
        @for ($i=1; $i <= $rows; $i++)
            <div class="flex gap-2">
                @for ($j=1; $j <= $columns; $j++)
                    <div class="w-12 h-12 border-2 border-gray-300 rounded-lg flex items-center justify-center">
                        {{ $get('column_label') === 'numeric' ? $numbers[$i - 1] : $alphabets[$j - 1]}}
                        {{ $get('row_label') === 'numeric' ? $numbers[$i - 1] :  $alphabets[$j - 1] }}
                    </div>
                    @if($j === (int) ($get('column_layout')[0]))
                        <div class="w-12 h-12 border-2 border-transparent"></div>
                    @endif
                @endfor
            </div>
        @endfor
    </div>
</x-filament::section>
