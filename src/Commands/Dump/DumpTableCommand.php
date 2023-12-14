<?php

namespace LaravelDev\Commands\Dump;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LaravelDev\Services\DBModelServices;

class DumpTableCommand extends Command
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
        $tableName = Str::of($tableName)->snake();

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

        return self::SUCCESS;
    }
}
