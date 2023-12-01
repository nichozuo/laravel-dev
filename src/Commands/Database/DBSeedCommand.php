<?php

namespace LaravelDev\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DBSeedCommand extends Command
{
    protected $signature = 'dbs';
    protected $description = 'iseed backup command';

    /**
     * @return int
     */
    public function handle(): int
    {
        collect(config('project.dbBackupList'))->each(function ($item) {
            $this->line("backup:::$item");
            Artisan::call("iseed $item --force");
        });

        return self::SUCCESS;
    }
}
