<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class BlockPublicFinancialDocuments
{
    public function handle(Request $request, Closure $next)
    {
        abort_if(preg_match('~^/?storage/(Invoices|PackingSlips)(/|$)~i', rawurldecode($request->path())), 404);

        return $next($request);
    }
}
