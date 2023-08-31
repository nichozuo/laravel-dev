<?php

namespace LaravelDev\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LaravelDev\Services\DBModelServices;

class GenAllModelsCommand extends Command
{
    protected $signature = 'gam';
    protected $description = 'Gen All Models';

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (DBModelServices::GetDBModel()->tableKeys as $name) {
            $this->line($name . ':::');
            Artisan::call("gf -d -f $name");
        }
    }
}