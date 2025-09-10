<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;
use Illuminate\Support\Str;

class Sort
{
    public function handle($query, Closure $next)
    {
        if (empty(request()->get('orderBy'))) {
            return $next($query)->orderBy('updated_at', 'DESC');
        }

        $orderByParts = Str::of(request()->get('orderBy'))
            ->explode(':')
            ->toArray();

        // Validate that we have both column and direction
        if (count($orderByParts) < 2) {
            return $next($query)->orderBy('updated_at', 'DESC');
        }

        [$column, $direction] = $orderByParts;

        // Validate direction
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? $direction : 'DESC';

        return $next($query)->orderBy($column, $direction);
    }
}
