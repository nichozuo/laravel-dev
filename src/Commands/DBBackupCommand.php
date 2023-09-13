<?php

namespace LaravelDev\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DBBackupCommand extends Command
{
    protected $signature = 'dbb';
    protected $description = 'iseed backup command';

    /**
     * @return int
     */
    public function handle(): int
    {
        $list = config('project.dbBackupList', []);
        foreach ($list as $item) {
            $this->line("backup:::$item");
            Artisan::call("iseed $item --force");
        }
        return 0;
    }
}
