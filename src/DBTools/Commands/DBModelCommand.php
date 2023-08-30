<?php

namespace LaravelDev\DBTools\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelDev\DBTools\DBToolsServices;

class DBModelCommand extends Command
{
    protected $signature = 'dm {table} {column?}';
    protected $description = 'dump table from db model';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $tableName = $this->argument('table');
        $columnName = $this->argument('column') ?? null;

        $table = DBToolsServices::GetTable($tableName);

        if ($columnName) {
            $columnName = str_replace('$', '', $columnName);
            dump($table->columns[$columnName]);
        } else {
            dump($table);
        }
        return 0;
    }
}