<?php

namespace LaravelDev\GenTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LaravelDev\DBTools\DBToolsServices;

class GenAllModelsCommand extends Command
{
    protected $signature = 'gam';
    protected $description = 'Gen All Models';

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (DBToolsServices::GetDBModel()->tables as $table) {
            $this->line($table->name . ':::');
            Artisan::call("gf -d -f $table->name");
        }
    }
}