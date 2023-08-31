<?php

namespace LaravelDev;

use Illuminate\Database\Schema\Blueprint;
use LaravelDev\Commands\DBBackupCommand;
use LaravelDev\Commands\DBCacheCommand;
use LaravelDev\Commands\DBDumpModelCommand;
use LaravelDev\Commands\DBDumpTableCommand;
use LaravelDev\Commands\GenAllEnumsCommand;
use LaravelDev\Commands\GenAllModelsCommand;
use LaravelDev\Commands\GenFilesCommand;
use LaravelDev\Commands\RenameMigrationFilesCommand;
use LaravelDev\Commands\RouterDumpCommand;
use LaravelDev\Commands\UpdateModelsCommand;


/**
 * @method addColumn(string $string, string $column, array $compact)
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        // commands
        $this->commands([
            DBCacheCommand::class,
            DBBackupCommand::class,
            DBDumpTableCommand::class,
            DBDumpModelCommand::class,

            RouterDumpCommand::class,

            GenFilesCommand::class,
            GenAllEnumsCommand::class,
            GenAllModelsCommand::class,
            RenameMigrationFilesCommand::class,
            UpdateModelsCommand::class,
        ]);

        // blueprint macros
        Blueprint::macro('xEnum', function (string $column, mixed $enumClass, string $comment) {
            $length = $enumClass::GetMaxLength();
            $allowed = $enumClass::columns();
            return $this->addColumn('enum', $column, compact('length', 'allowed'))->comment($enumClass::comment($comment));
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path("project.php"),
            __DIR__ . '/docs' => public_path("docs"),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/api.php');
    }
}
