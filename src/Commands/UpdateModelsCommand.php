<?php

namespace LaravelDev\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use LaravelDev\Services\DBModelServices;

class UpdateModelsCommand extends Command
{
    protected $signature = 'update:models';
    protected $description = 'Command description';

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (DBModelServices::GetDBModel()->tables as $table) {
            $name = $table->name;
            $this->line($name . ':::');
            Artisan::call("gf $name -d -f");
        }
    }
}
