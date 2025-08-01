<?php

use App\Livewire\SearchResult;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/search', SearchResult::class)
    ->name('search.results');