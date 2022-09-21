<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;

class Collection
{
    public function handle($query, Closure $next)
    {
        if (!request()->has('collection')) {
            return $next($query);
        }

        return $next($query)->where('collection_name', request()->get('collection'));
    }
}
