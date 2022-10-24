<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Search
{
    public function handle($query, Closure $next)
    {
        if (empty(request()->get('search'))) {
            return $next($query);
        }

        $search = ['%', str_replace('*', '%', request()->get('search')), '%'];
        return $next($query)
            ->where('file_name', 'like', Arr::join($search, ''))
            ->orWhere('data', 'like', Arr::join($search, ''));
    }
}
