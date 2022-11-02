<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Search
{
    public function handle($query, Closure $next)
    {
        $search = request()->get('search');

        if (empty($search)) return $next($query);

        $search = Str::upper($search);
        $search = Str::replace('*', '%', $search);
        $search = "%{$search}%";

        return $next($query)->where(
            fn ($subQuery) => $subQuery
                ->where(DB::raw('UPPER(file_name)'), 'LIKE', $search)
                ->orWhere(DB::raw('UPPER(data)'), 'LIKE', $search)
        );
    }
}
