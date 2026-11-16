<?php

namespace App\Observers;

use App\Services\Storefront\CatalogInvalidation;
use Illuminate\Database\Eloquent\Model;

final class CatalogChangeObserver
{
    public function saved(Model $model): void
    {
        app(CatalogInvalidation::class)->changed();
    }

    public function deleted(Model $model): void
    {
        app(CatalogInvalidation::class)->changed();
    }

    public function restored(Model $model): void
    {
        app(CatalogInvalidation::class)->changed();
    }
}
