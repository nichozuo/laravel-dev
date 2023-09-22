<?php

namespace LaravelDev\App\Libraries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use LaravelDev\App\Exceptions\Err;
use LaravelDev\App\Exceptions\ErrConst;

class QueryHelper
{
    /**
     * @param Builder $builder
     * @return LengthAwarePaginator
     * @throws Err
     */
    public static function scopePage(Builder $builder): LengthAwarePaginator
    {
        $perPage = request()->validate([
            'perPage' => 'nullable|integer',
        ])['perPage'] ?? 10;

        $allow = config('project.perPageAllow', [10, 20, 50, 100]);
        if (!in_array($perPage, $allow))
            ee(...ErrConst::PerPageIsNotAllow);

        return $builder->paginate($perPage);
    }

    /**
     * @param Builder $builder
     * @param array $params
     * @param string $key
     * @param string|null $field
     * @return void
     */
    public static function scopeIfWhere(Builder $builder, array $params, string $key, ?string $field = null): void
    {
        if ($params[$key] ?? false) {
            $builder->where($field ?? $key, $params[$key]);
        }
    }
}