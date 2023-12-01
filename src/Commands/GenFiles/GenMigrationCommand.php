<?php

namespace LaravelDev\Commands\GenFiles;

use Illuminate\Support\Str;
use LaravelDev\App\Exceptions\Err;

class GenMigrationCommand extends Base
{
    protected $name = 'gm';
    protected $description = "根据输入的数据库表名，生成migration迁移文件。
    表名：会转成蛇形，单数，复数。
    例如：php artisan gm users
    例如：php artisan gm User";

    /**‘
     * @return int
     * @throws Err
     */
    public function handle(): int
    {
        list($name,) = $this->getNameAndForce();

        $tableName = Str::of($name)->snake()->singular()->plural();

        $this->call('make:migration', [
            'name' => "create_{$tableName}_table",
            '--create' => $tableName,
            '--table' => $tableName,
        ]);

        return self::SUCCESS;
    }
}