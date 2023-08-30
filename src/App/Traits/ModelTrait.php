<?php

namespace LaravelDev\App\Traits;


use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelDev\App\Exceptions\ErrConst;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use LaravelDev\App\Exceptions\Err;

/**
 * @method static ifWhereLike(array $params, string $key, ?string $field = null): Builder
 * @method static ifWhere(array $params, string $key, ?string $field = null): Builder
 * @method static order(string $key = 'orderBy'): Builder
 * @method static page(): LengthAwarePaginator
 * @method static self|null getById(int $id, bool $throw = true, bool $lock = false)
 * @method static self|null idp(array $params, bool $throw = true, bool $lock = false)
 * @method static unique(array $params, array $keys, string $label = null, string $field = 'id'): Builder
 *
 * @method static create(array $params)
 * @method static where(string $field, string $value)
 * @method static findOrFail(int $id)
 * @method static selectRaw(string $raw)
 * @method static whereIn(string $field, array $array)
 * @method static defaultOrder()
 * @method children()
 * @method fixTree()
 * @method up()
 * @method down()
 */
trait ModelTrait
{
    /**
     * @param Builder $builder
     * @param array $params 请求参数
     * @param string $key 请求参数的key
     * @param string|null $field 字段名
     * @return Builder
     */
    public function scopeIfWhere(Builder $builder, array $params, string $key, ?string $field = null): Builder
    {
        return ($params[$key] ?? false) ? $builder->where($field ?? $key, $params[$key]) : $builder;
    }

    /**
     * @param Builder $builder
     * @param array $params 请求参数
     * @param string $key 请求参数的key
     * @param string|null $field 字段名
     * @return Builder
     */
    public function scopeIfWhereLike(Builder $builder, array $params, string $key, ?string $field = null): Builder
    {
        return ($params[$key] ?? false) ? $builder->where($field ?? $key, 'like', "%$params[$key]%") : $builder;
    }

    /**
     * @param Builder $builder
     * @param array $params
     * @param string $key
     * @param array $fields
     * @return Builder
     */
    public function scopeIfWhereKeyword(Builder $builder, array $params, string $key, array $fields): Builder
    {
        $value = $params[$key] ?? null;
        if ($value == '') $value = null;
        if ($value) {
            return $builder->where(function ($q) use ($value, $fields) {
                foreach ($fields as $field) {
                    $q->orWhere($field, 'like', "%$value%");
                }
            });
        } else {
            return $builder;
        }
    }

    /**
     * @param Builder $builder
     * @param string $key
     * @return Builder
     */
    public function scopeOrder(Builder $builder, string $key = 'sorter'): Builder
    {
        $params = request()->validate([
            $key => 'nullable|array',
        ]);
        if ($params[$key] ?? false) {
            $orderBy = $params[$key];
            if (count($orderBy) == 2) {
                $field = $orderBy[0];
                $sort = $orderBy[1] == 'descend' ? 'desc' : 'asc';
                return $builder->orderBy($field, $sort);
            }
        }
        return $builder->orderByDesc('id');
    }

    /**
     * @param Builder $builder
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function scopePage(Builder $builder): LengthAwarePaginator
    {
        $perPage = request()->validate([
                'perPage' => 'nullable|integer',
            ])['perPage'] ?? 10;

        $allow = config('common.perPageAllow', [10, 20, 50, 100]);
        if (!in_array($perPage, $allow))
            ee(...ErrConst::PerPageIsNotAllow);

        return $builder->paginate($perPage);
    }

    /**
     * @param Builder $builder
     * @param int $id
     * @param bool $throw
     * @param bool $lock
     * @return self|null
     */
    public function scopeGetById(Builder $builder, int $id, bool $throw = true, bool $lock = false): self|null
    {
        $builder = $builder->where('id', $id);
        if ($lock) {
            $builder = $builder->lockForUpdate();
        }
        if ($throw) {
            return $builder->firstOrFail();
        }
        return $builder->first();
    }

    /**
     * @param Builder $builder
     * @param array $params
     * @param string $key
     * @param bool $throw
     * @param bool $lock
     * @return ModelTrait|null
     */
    public function scopeIdp(Builder $builder, array $params, string $key = 'id', bool $throw = true, bool $lock = false): self|null
    {
        return $builder->getById($params['id'], throw: $throw, lock: $lock);
    }

    /**
     * @param Builder $builder
     * @param array $params
     * @param array $keys
     * @param string|null $label
     * @param string $field
     * @return Builder
     * @throws Exception
     */
    public function scopeUnique(Builder $builder, array $params, array $keys, string $label = null, string $field = 'id'): Builder
    {
        $data = Arr::only($params, $keys);
        $model = $builder->where($data)->first();
        if ($model && $label != null) {
            if (!isset($params[$field]) || $model->$field != $params[$field])
                throw new Exception("{$label}【{$params[$keys[0]]}】已存在，请重试");
        }
        return $builder;
    }

}

