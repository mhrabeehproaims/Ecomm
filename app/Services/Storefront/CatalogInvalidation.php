<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class CatalogInvalidation
{
    private bool $dirty = false;

    private bool $registered = false;

    public function changed(): void
    {
        if (! config('catalog.revalidate_url') || strlen(config('catalog.revalidate_secret', '')) < 32) {
            return;
        }
        DB::afterCommit(function () {
            $this->dirty = true;
            if ($this->registered) {
                return;
            }
            $this->registered = true;
            app()->terminating(fn () => $this->flush());
        });
    }

    public function flush(): void
    {
        if (! $this->dirty) {
            return;
        }
        $this->dirty = false;
        try {
            Http::withToken(config('catalog.revalidate_secret'))->acceptJson()->connectTimeout(1)->timeout(2)
                ->post(config('catalog.revalidate_url'))->throw();
        } catch (\Throwable $error) {
            Log::warning('Storefront catalog invalidation unavailable; timed refresh remains active.');
        }
    }
}
