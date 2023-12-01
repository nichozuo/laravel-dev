<?php

namespace LaravelDev\Commands\GenFiles;

use Illuminate\Support\Str;
use LaravelDev\App\Exceptions\Err;
use LaravelDev\Services\GenFilesServices;
use ReflectionException;

class GenTestCommand extends Base
{
    protected $name = 'gt';
    protected $description = "根据输入的路径，生成控制器的测试文件，包含所有方案和请求参数。路径通过斜杠/拆分成[模块名]和[表名]。
    模块名：会转成大写开头的驼峰，斜杠/分割成数组，支持多级目录；
    表名：会转成大写开头的驼峰；
    例如：php artisan gt admin/users
    例如：php artisan gt Admin/auth/CompanyAdmin";

    /**
     * @return int
     * @throws Err
     * @throws ReflectionException
     */
    public function handle(): int
    {
        list($name, $force) = $this->getNameAndForce();

        $arr = explode('/', $name);

        $tableName = Str::of(array_pop($arr))->studly()->toString();//->singular()->plural()->toString();

        $modulesName = array_map(function ($value) {
            return Str::of($value)->studly()->toString();
        }, $arr);

        GenFilesServices::GenTest($modulesName, $tableName, $force);

        return self::SUCCESS;
    }
}