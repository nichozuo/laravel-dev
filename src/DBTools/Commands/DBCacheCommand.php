<?php

namespace LaravelDev\DBTools\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use LaravelDev\DBTools\DBToolsServices;

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
        DBToolsServices::CacheDBModel();
        $this->line('db cached...');
        return 0;
    }
}