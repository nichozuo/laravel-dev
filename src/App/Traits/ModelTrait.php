<?php

namespace LaravelDev\App\Traits;


use Closure;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use LaravelDev\App\Exceptions\Err;
use LaravelDev\App\Exceptions\ErrConst;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Carbon\Carbon;

/**
 * @method static ifWhere(array $params, string $key, ?string $field = null)
 * @method static ifWhereLike(array $params, string $key, ?string $field = null)
 * @method static ifWhereLikeKeyword(array $params, string $key, array $fields)
 * @method static ifWhereNumberRange(array $params, string $key, ?string $field = null)
 * @method static ifWhereDateRange(array $params, string $key, ?string $field = null, ?string $type = 'datetime')
 * @method static ifHasWhereLike(array $params, string $key, string $relation, ?string $field = null)
 * @method static order(string $key = 'sorter')
 * @method static unique(array $params, array $keys, string $label = null, string $field = 'id')
 * @method static forSelect(?string $key1 = 'id', ?string $key2 = 'name')
 * @method static page()
 * @method static getById(int $id, bool $throw = true, bool $lock = false)
 *
 * @method static lockForUpdate()
 * @method static create(array $params)
 * @method static where(string $field, string $value, ?string $value)
 * @method static findOrFail(int $id)
 * @method static selectRaw(string $raw)
 * @method static whereIn(string $field, array $array)
 * @method static defaultOrder()
 * @method static updateOrCreate(string[] $array, array[] $array1)
 * @method static each(Closure $param)
 * @method children()
 * @method fixTree()
 * @method up()
 * @method down()
 * @method static whereNull(string $string)
 */
trait ModelTrait
{
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param string $key
//     * @param array $fields
//     * @return Builder
//     */
//    public function scopeIfWhereLikeKeyword(Builder $builder, array $params, string $key, array $fields): Builder
//    {
//        $value = $params[$key] ?? null;
//        if ($value == '') $value = null;
//        if ($value) {
//            return $builder->where(function ($q) use ($value, $fields) {
//                foreach ($fields as $field) {
//                    $q->orWhere($field, 'like', "%$value%");
//                }
//            });
//        } else {
//            return $builder;
//        }
//    }
//
//    /**
//     * @param Builder $builder
//     * @param string|null $key1
//     * @param string|null $key2
//     * @return Builder[]|Collection
//     */
//    public function scopeForSelect(Builder $builder, ?string $key1 = 'id', ?string $key2 = 'name'): Collection|array
//    {
//        return $builder->selectRaw("$key1, $key2")->get();
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param string $key
//     * @param string|null $field
//     * @param string|null $type
//     * @return Builder
//     * @throws Err
//     */
//    public function scopeIfWhereDateRange(Builder $builder, array $params, string $key, ?string $field = null, ?string $type = 'datetime'): Builder
//    {
//        if (!isset($params[$key]))
//            return $builder;
//
//        $range = $params[$key];
//        if (count($range) != 2)
//            ee("{$key}参数必须是两个值");
//
//        $start = $range[0] == '' || $range[0] == null ? null : Carbon::parse($range[0]);
//        $end = $range[1] == '' || $range[1] == null ? null : Carbon::parse($range[1]);
//
//        $start = $start ? ($type == 'date' ? $start->toDateString() : $start->startOfDay()->toDateTimeString()) : null;
//        $end = $end ? ($type == 'date' ? $end->toDateString() : $end->endOfDay()->toDateTimeString()) : null;
//
//        $field = $field ?? $key;
//        if ($start && !$end)
//            return $builder->where($field, '>=', $start);
//        if (!$start && $end)
//            return $builder->where($field, '<=', $end);
//        else
//            return $builder->whereBetween($field, [$start, $end]);
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param string $key
//     * @param string|null $field
//     * @return Builder
//     * @throws Err
//     */
//    public function scopeIfWhereNumberRange(Builder $builder, array $params, string $key, ?string $field = null): Builder
//    {
//        if (!isset($params[$key]))
//            return $builder;
//
//        $dataRange = $params[$key];
//        if (count($dataRange) != 2)
//            ee("{$key}参数必须是两个值");
//
//        $start = $dataRange[0] ?? null;
//        $end = $dataRange[1] ?? null;
//
//        if ($start && !$end)
//            return $builder->where($field ?? $key, '>=', $start);
//        if (!$start && $end)
//            return $builder->where($field ?? $key, '<=', $end);
//        else
//            return $builder->whereBetween($field ?? $key, [$start, $end]);
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param string $key
//     * @param string $relationName
//     * @param string $field
//     * @return Builder
//     */
//    public function scopeIfHasWhereLike(Builder $builder, array $params, string $key, string $relationName, string $field): Builder
//    {
//        if (!isset($params[$key]))
//            return $builder;
//
//        return $builder->whereHas($relationName, function ($q) use ($params, $key, $field) {
//            $q->where($field, 'like', "%$params[$key]%");
//        });
//    }
//
//    /**
//     * @param array $params
//     * @param string $key
//     * @return bool
//     */
//    private function valid(array $params, string $key): bool
//    {
//        return array_key_exists($key, $params) && !empty($params[$key]);
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params 请求参数
//     * @param string $key 请求参数的key
//     * @param string|null $field 字段名
//     * @return Builder
//     */
//    public function scopeIfWhere(Builder $builder, array $params, string $key, ?string $field = null): Builder
//    {
//        return ($this->valid($params, $key)) ? $builder->where($field ?? $key, $params[$key]) : $builder;
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params 请求参数
//     * @param string $key 请求参数的key
//     * @param string|null $field 字段名
//     * @return Builder
//     */
//    public function scopeIfWhereLike(Builder $builder, array $params, string $key, ?string $field = null): Builder
//    {
//        return ($this->valid($params, $key)) ? $builder->where($field ?? $key, 'like', "%$params[$key]%") : $builder;
//    }
//
////    /**
////     * @param Builder $builder
////     * @param array $params
////     * @param string $key
////     * @param array $fields
////     * @return Builder
////     */
////    public function scopeIfWhereKeyword(Builder $builder, array $params, string $key, array $fields): Builder
////    {
////        $value = $params[$key] ?? null;
////        if ($value == '') $value = null;
////        if ($value) {
////            return $builder->where(function ($q) use ($value, $fields) {
////                foreach ($fields as $field) {
////                    $q->orWhere($field, 'like', "%$value%");
////                }
////            });
////        } else {
////            return $builder;
////        }
////    }
//
//    /**
//     * @param Builder $builder
//     * @param string $key
//     * @return Builder
//     */
//    public function scopeOrder(Builder $builder, string $key = 'sorter'): Builder
//    {
//        $params = request()->validate([
//            $key => 'nullable|array',
//        ]);
//        if ($params[$key] ?? false) {
//            $orderBy = $params[$key];
//            if (count($orderBy) == 2) {
//                $field = $orderBy[0];
//                $sort = $orderBy[1] == 'descend' ? 'desc' : 'asc';
//                return $builder->orderBy($field, $sort);
//            }
//        }
//        return $builder->orderByDesc('id');
//    }
//
//    /**
//     * @param Builder $builder
//     * @return LengthAwarePaginator
//     * @throws Exception
//     */
//    public function scopePage(Builder $builder): LengthAwarePaginator
//    {
//        $perPage = request()->validate([
//            'perPage' => 'nullable|integer',
//        ])['perPage'] ?? 10;
//
//        $allow = config('project.perPageAllow', [10, 20, 50, 100]);
//        if (!in_array($perPage, $allow))
//            ee(...ErrConst::PerPageIsNotAllow);
//
//        return $builder->paginate($perPage);
//    }
//
//    /**
//     * @param Builder $builder
//     * @param int $id
//     * @param bool $throw
//     * @param bool $lock
//     * @return self|null
//     */
//    public function scopeGetById(Builder $builder, int $id, bool $throw = true, bool $lock = false): self|null
//    {
//        $builder = $builder->where('id', $id);
//        if ($lock) {
//            $builder = $builder->lockForUpdate();
//        }
//        if ($throw) {
//            return $builder->firstOrFail();
//        }
//        return $builder->first();
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param string $key
//     * @param bool $throw
//     * @param bool $lock
//     * @return ModelTrait|null
//     */
//    public function scopeIdp(Builder $builder, array $params, string $key = 'id', bool $throw = true, bool $lock = false): self|null
//    {
//        return $builder->getById($params[$key], throw: $throw, lock: $lock);
//    }
//
//    /**
//     * @param Builder $builder
//     * @param array $params
//     * @param array $keys
//     * @param string|null $label
//     * @param string $field
//     * @return Builder
//     * @throws Exception
//     */
//    public function scopeUnique(Builder $builder, array $params, array $keys, string $label = null, string $field = 'id'): Builder
//    {
//        $data = Arr::only($params, $keys);
//        $model = $builder->where($data)->first();
//        if ($model && $label != null) {
//            if (!isset($params[$field]) || $model->$field != $params[$field])
//                throw new Exception("{$label}【{$params[$keys[0]]}】已存在，请重试");
//        }
//        return $builder;
//    }

}

