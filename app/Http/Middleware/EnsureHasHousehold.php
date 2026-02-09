<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasHousehold
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->household_id) {
            return redirect()->route('household.show')
                ->with('error', 'Du musst zuerst einem Haushalt beitreten oder einen erstellen.');
        }

        return $next($request);
    }
}
