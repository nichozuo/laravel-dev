<?php

namespace LaravelDev\Commands\Dump;

use Exception;
use Illuminate\Console\Command;
use LaravelDev\Services\DBModelServices;

class DumpDatabaseCommand extends Command
{
    protected $signature = 'dd {table} {column?}';
    protected $description = 'dump table from db model';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $tableName = $this->argument('table');
        $columnName = $this->argument('column') ?? null;

        $table = DBModelServices::GetTable($tableName);

        if ($columnName) {
            $columnName = str_replace('$', '', $columnName);
            dump($table->columns[$columnName]);
        } else {
            dump($table);
        }
        return self::SUCCESS;
    }
}