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
            function ($subQuery) use ($search) {
                $dataColumn = DB::raw("UPPER(data)"); // Mysql
                $subQuery->where(DB::raw('UPPER(file_name)'), 'LIKE', $search);

                if (config('database.default') === 'pgsql') {
                    // Postgres
                    $dataColumn = DB::raw('UPPER("data"::text)');
                }

                $subQuery->orWhere($dataColumn, 'LIKE', $search);

                return $subQuery;
            }
        );
    }
}
