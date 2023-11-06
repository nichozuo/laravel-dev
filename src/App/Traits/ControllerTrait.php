<?php

namespace LaravelDev\App\Traits;


use Exception;
use LaravelDev\App\Exceptions\Err;

trait ControllerTrait
{
    /**
     * @param array $params
     * @param string $key
     */
    protected function crypto(array &$params, string $key = 'password'): void
    {
        if (isset($params[$key])) {
            if ($params[$key] == '')
                unset($params[$key]);
            else
                $params[$key] = bcrypt($params[$key]);
        }
    }

    /**
     * @intro 获得分页size
     * @return int
     * @throws Err
     */
    protected function perPage(): int
    {
        $params = request()->only('perPage');
        if (!isset($params['perPage']) || !is_numeric($params['perPage']))
            return 20;

        $allow = config('project.perPageAllow', [10, 20, 50, 100,500,1000]);
        if (!in_array($params['perPage'], $allow))
            Err::Throw('分页数据不在规定范围内');

        return (int)$params['perPage'];
    }


}
