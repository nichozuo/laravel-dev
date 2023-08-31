<?php

namespace LaravelDev\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use LaravelDev\Services\DBModelServices;

class DBCacheCommand extends Command
{
    protected $signature = 'dbc';
    protected $description = 'Cache DBModel to disk';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        DBModelServices::ForceCache();
        $this->line('db cached...');
        return 0;
    }
}