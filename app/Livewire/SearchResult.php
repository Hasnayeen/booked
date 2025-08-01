<?php

namespace App\Livewire;

use Livewire\Component;

class SearchResult extends Component
{
    public function mount()
    {
        dd(request()->all());
    }

    public function render()
    {
        return view('livewire.search-result');
    }
}
