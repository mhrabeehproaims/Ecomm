<?php

namespace App\Services\Storefront;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OperationalAlert
{
    public static function record(string $code, string $reference): void
    {
        $id = hash('sha256', $code.'|'.$reference);
        DB::table('storefront_operational_alerts')->insertOrIgnore(['id' => $id, 'code' => $code, 'reference' => $reference, 'occurrences' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('storefront_operational_alerts')->where('id', $id)->increment('occurrences', 1, ['resolved_at' => null, 'updated_at' => now()]);
        // Deliberately exclude exception messages, provider payloads and customer data.
        Log::warning('Storefront financial operation needs review', ['code' => $code, 'reference' => $reference]);
    }

    public static function resolve(string $code, string $reference): void
    {
        DB::table('storefront_operational_alerts')->where('id', hash('sha256', $code.'|'.$reference))->whereNull('resolved_at')->update(['resolved_at' => now(), 'updated_at' => now()]);
    }
}
