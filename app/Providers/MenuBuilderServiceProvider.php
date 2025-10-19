<?php

namespace App\Providers;

use App\Models\Journal;
use Biostate\MenuBuilder\Facades\MenuBuilder;
use Illuminate\Support\ServiceProvider;

class MenuBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ننتظر حتى يتم تحميل التطبيق كاملًا
        $this->app->booted(function () {
            if (! \Schema::hasTable('journals')) {
                return;
            }

            $journals = Journal::all();

            foreach ($journals as $journal) {
                MenuBuilder::addLocation(
                    $journal->slug,
                    $journal->name
                );
            }

        });
    }
}
