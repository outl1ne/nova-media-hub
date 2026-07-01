<?php

namespace Outl1ne\NovaMediaHub\Filters;

use Closure;

class Sort
{
    private const DEFAULT_ORDER_COLUMN = 'updated_at';
    private const DEFAULT_ORDER_DIRECTION = 'desc';
    private const ALLOWED_ORDER_COLUMNS = ['updated_at', 'created_at'];
    private const ALLOWED_ORDER_DIRECTIONS = ['asc', 'desc'];

    public function handle($query, Closure $next)
    {
        [$column, $direction] = $this->resolveOrderBy(request()->get('orderBy'));

        return $next($query)->orderBy($column, $direction);
    }

    private function resolveOrderBy($rawOrderBy): array
    {
        if (!is_string($rawOrderBy) || trim($rawOrderBy) === '') {
            return [self::DEFAULT_ORDER_COLUMN, self::DEFAULT_ORDER_DIRECTION];
        }

        [$column, $direction] = array_pad(explode(':', $rawOrderBy, 2), 2, '');

        $column = trim($column);
        $direction = strtolower(trim($direction));

        if (!in_array($column, self::ALLOWED_ORDER_COLUMNS, true)) {
            return [self::DEFAULT_ORDER_COLUMN, self::DEFAULT_ORDER_DIRECTION];
        }

        if (!in_array($direction, self::ALLOWED_ORDER_DIRECTIONS, true)) {
            $direction = self::DEFAULT_ORDER_DIRECTION;
        }

        return [$column, $direction];
    }
}
