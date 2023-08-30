<?php

namespace LaravelDev\DBTools\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelDev\DBTools\DBToolsServices;

class DBDumpCommand extends Command
{
    protected $signature = 'dt {table}';
    protected $description = 'dump the fields of the table';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $tableName = $this->argument('table');

        $table = DBToolsServices::GetTable($tableName);

        $this->warn('Gen Table template');
        $this->line("protected \$table = '$table->name';");
        $this->line("protected string \$comment = '$table->comment';");
        $this->line("protected \$fillable = [$table->fillableString];");
        $this->line($table->castsString);

        $this->warn('gen Validate template');
        $this->line($table->validateString);

        $this->warn('gen Insert template');
        $this->line($table->insertString);

        return 0;
    }
}
