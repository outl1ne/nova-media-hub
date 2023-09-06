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

        return $next($query)->where(
            function ($subQuery) use ($search) {
                $searchLike = Str::lower($search);
                $searchLike = Str::replace('*', '%', $searchLike);
                $searchLike = "%{$searchLike}%";

                $dataColumn = DB::raw("LOWER(data)"); // Mysql
                $subQuery->where(DB::raw('LOWER(file_name)'), 'LIKE', $searchLike);

                if (config('database.default') === 'pgsql') {
                    // Postgres
                    $dataColumn = DB::raw('LOWER("data"::text)');
                }

                $subQuery->orWhere($dataColumn, 'LIKE', $searchLike);

                if (is_numeric($search)) {
                    // possibly searching by ID only
                    $subQuery->orWhere('id', $search);
                }

                return $subQuery;
            }
        );
    }
}
