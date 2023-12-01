<?php

namespace LaravelDev\Commands\GenFiles;

use Exception;
use Illuminate\Support\Str;
use LaravelDev\Services\GenFilesServices;

class GenModelCommand extends Base
{
    protected $name = 'gd';
    protected $description = "根据输入的数据库表名，生成模型文件。
    表名：会转成蛇形，单数，复数。
    例如：php artisan gd users -F
    例如：php artisan gd User";

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        list($name, $force) = $this->getNameAndForce();

        $tableName = Str::of($name)->snake()->singular()->plural();

        GenFilesServices::GenModels($tableName, $force);

        return self::SUCCESS;
    }
}