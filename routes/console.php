<?php

use App\Models\Action;
use App\Models\Decision;
use App\Models\File;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Support\Facades\Artisan;

Artisan::command('about-project', function (): void {
    $this->info('Private Project Memory application base is installed.');
})->purpose('Display the project bootstrap status');

Artisan::command('search:reindex', function (): int {
    $models = [
        Project::class,
        Note::class,
        Decision::class,
        Action::class,
        File::class,
    ];

    $this->info('Rebuilding Meilisearch indexes...');

    foreach ($models as $model) {
        $this->line("Flushing {$model}");
        Artisan::call('scout:flush', ['model' => $model]);
        $this->output->write(Artisan::output());

        $this->line("Importing {$model}");
        Artisan::call('scout:import', ['model' => $model]);
        $this->output->write(Artisan::output());
    }

    $this->info('Search indexes rebuilt.');

    return 0;
})->purpose('Rebuild all MVP search indexes');
