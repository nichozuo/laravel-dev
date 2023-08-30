<?php

namespace LaravelDev;

use Illuminate\Database\Schema\Blueprint;
use LaravelDev\DBTools\Commands\DBBackupCommand;
use LaravelDev\DBTools\Commands\DBCacheCommand;
use LaravelDev\DBTools\Commands\DBDumpCommand;
use LaravelDev\DBTools\Commands\DBModelCommand;
use LaravelDev\GenTools\Commands\GenAllEnumsCommand;
use LaravelDev\GenTools\Commands\GenAllModelsCommand;
use LaravelDev\GenTools\Commands\GenFilesCommand;
use LaravelDev\GenTools\Commands\RenameMigrationFilesCommand;
use LaravelDev\GenTools\Commands\UpdateModelsCommand;


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
            DBDumpCommand::class,
            DBModelCommand::class,
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

        Blueprint::macro('xPercent', function (string $column, $total = 5, $places = 2, $unsigned = false) {
            return $this->addColumn('float', $column, compact('total', 'places', 'unsigned'));
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/App/common.php' => config_path("common.php"),
            __DIR__ . '/DocTools/docs' => public_path("docs"),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/DocTools/api.php');
    }
}
