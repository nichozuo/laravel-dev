<?php

namespace LaravelDev\Commands\GenFiles;

use Illuminate\Console\Command;
use LaravelDev\App\Exceptions\Err;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Base extends Command
{
    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::OPTIONAL, '名称参数'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, '是否强制覆盖'],
        ];
    }

    /**
     * @return array{string, bool}
     * @throws Err
     */
    protected function getNameAndForce(): array
    {
        $name = $this->argument('name');
        $force = $this->option('force');
        if (!$name)
            ee("名称必须填写");
        return [$name, $force];
    }
}