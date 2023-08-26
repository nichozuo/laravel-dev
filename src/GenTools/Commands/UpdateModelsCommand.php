<?php

namespace LaravelDev\GenTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LaravelDev\DBTools\DBToolsServices;

class UpdateModelsCommand extends Command
{
    protected $signature = 'update:models';
    protected $description = 'Command description';

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (DBToolsServices::GetDBModel()->tables as $table) {
            $name = $table->name;
            $this->line($name . ':::');
            Artisan::call("gf $name -d -f");
        }
    }
}
