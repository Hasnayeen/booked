<?php

declare(strict_types=1);

use App\Models\Operator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('operators:assign-logos', function (): void {
    $this->info('Starting logo assignment for operators...');

    $logoFiles = collect(Storage::disk('public')->files('logo'))
        ->filter(fn (string $file): bool => str_ends_with($file, '.svg'))
        ->map(fn (string $file): string => basename($file))
        ->values()
        ->toArray();

    if (empty($logoFiles)) {
        $this->error('No SVG logo files found in storage/app/public/logo directory');

        return;
    }

    $this->info(sprintf('Found %d logo files: %s', count($logoFiles), implode(', ', $logoFiles)));

    $operators = Operator::all();

    if ($operators->isEmpty()) {
        $this->error('No operators found in the database');

        return;
    }

    $this->info(sprintf('Found %d operators to update', $operators->count()));

    $bar = $this->output->createProgressBar($operators->count());
    $bar->start();

    $updatedCount = 0;

    foreach ($operators as $operator) {
        $randomLogo = $logoFiles[array_rand($logoFiles)];
        $logoName = pathinfo((string) $randomLogo, PATHINFO_FILENAME);

        $operator->update(['logo' => $logoName]);

        $updatedCount++;
        $bar->advance();
    }

    $bar->finish();
    $this->newLine();
    $this->info(sprintf('Successfully assigned logos to %d operators!', $updatedCount));
})->purpose('Assign random SVG logos to all operators');
