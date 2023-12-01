<?php

namespace LaravelDev\Commands\GenFiles;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LaravelDev\Services\DBModelServices;

class GenAllModelsCommand extends Command
{
    protected $signature = 'gam';
    protected $description = 'Gen All Models';

    /**
     * @return int
     */
    public function handle(): int
    {
        foreach (DBModelServices::GetDBModel()->tableKeys as $name) {
            $this->line($name . ':::');
            Artisan::call("gd -f $name");
        }

        return self::SUCCESS;
    }
}