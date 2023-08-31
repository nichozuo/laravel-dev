<?php

namespace LaravelDev\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelDev\Services\DBModelServices;

class DBDumpTableCommand extends Command
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

        $table = DBModelServices::GetTable($tableName);
        $fillable = implode("', '", $table->fillable);

        $this->warn('Gen Table template');
        $this->line("protected \$table = '$table->name';");
        $this->line("protected string \$comment = '$table->comment';");
        $this->line("protected \$fillable = ['$fillable'];");

        $this->warn('gen Validate template');
        $this->line(implode("\n", $table->validate));

        $this->warn('gen Insert template');
        $this->line(implode("\n", $table->insert));

        return 0;
    }
}
