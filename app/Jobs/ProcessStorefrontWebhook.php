<?php

namespace App\Jobs;

use App\Services\Storefront\WebhookProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessStorefrontWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public string $eventId) {}

    public function handle(WebhookProcessor $processor): void
    {
        $processor->process($this->eventId);
    }

    public function failed(?\Throwable $error): void
    {
        \App\Services\Storefront\OperationalAlert::record('webhook_job_failed', $this->eventId);
    }
}
