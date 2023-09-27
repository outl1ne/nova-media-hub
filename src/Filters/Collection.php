<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;
use Illuminate\Support\Facades\DB;
use Outl1ne\NovaMediaHub\Models\Media;

class Collection
{
    public function handle($query, Closure $next)
    {
        if (empty(request()->get('collection'))) {
            return $next($query);
        }

        $collectionName = request()->get('collection');
        $connectionName = Media::getConnectionResolver()->getDefaultConnection();
        $isProperSql = in_array($connectionName, ['mysql', 'pgsql', 'sqlite']);

        $queryWhere = $isProperSql ? 'LOWER(collection_name)' : 'collection_name';
        $queryValue = $isProperSql ? mb_strtolower($collectionName) : $collectionName;

        return $next($query)->where(DB::raw($queryWhere), '=', $queryValue);
    }
}
