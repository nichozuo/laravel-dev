<?php

namespace LaravelDev\Commands\GenFiles;

use Illuminate\Support\Str;
use LaravelDev\App\Exceptions\Err;
use LaravelDev\Services\GenFilesServices;

class GenEnumCommand extends Base
{
    protected $name = 'ge';
    protected $description = "根据输入的名称，生成enum文件。
    如果：名称中存在/，则根据/分割成【表名】/【字段名】，转成大写驼峰，再生成文件。
    例如：php artisan ge users/type => UsersTypeEnum

    如果：名称中不存在/，则直接根据名称生成文件。
    例如：php artisan ge UsersTypeEnum";

    /**
     * @return int
     * @throws Err
     */
    public function handle(): int
    {
        list($name, $force) = $this->getNameAndForce();

        if (Str::of($name)->contains('/')) {
            $name = Str::of($name)->explode('/')->map(function ($item) {
                return Str::of($item)->studly();
            })->implode('');
        } else {
            $name = Str::of($name)->studly();
        }
        if (!$name->endsWith('Enum'))
            $name = $name->append('Enum');

        GenFilesServices::GenEnum($name, $force);

        return self::SUCCESS;
    }
}