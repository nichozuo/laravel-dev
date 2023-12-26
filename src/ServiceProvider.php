<?php

namespace LaravelDev;

use Illuminate\Database\Schema\Blueprint;
use LaravelDev\App\Macros\BuilderMacros;
use LaravelDev\Commands\Database\DBCacheCommand;
use LaravelDev\Commands\Database\DBSeedCommand;
use LaravelDev\Commands\Dump\DumpDatabaseCommand;
use LaravelDev\Commands\Dump\DumpRouterCommand;
use LaravelDev\Commands\Dump\DumpTableCommand;
use LaravelDev\Commands\GenFiles\GenAllModelsCommand;
use LaravelDev\Commands\GenFiles\GenControllerCommand;
use LaravelDev\Commands\GenFiles\GenEnumCommand;
use LaravelDev\Commands\GenFiles\GenMigrationCommand;
use LaravelDev\Commands\GenFiles\GenModelCommand;
use LaravelDev\Commands\GenFiles\GenTestCommand;
use LaravelDev\Commands\RenameMigrationFilesCommand;


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
            DBSeedCommand::class,

            DumpDatabaseCommand::class,
            DumpRouterCommand::class,
            DumpTableCommand::class,

            GenAllModelsCommand::class,
            GenControllerCommand::class,
            GenEnumCommand::class,
            GenMigrationCommand::class,
            GenModelCommand::class,
            GenTestCommand::class,

            RenameMigrationFilesCommand::class,
        ]);

        //
        BuilderMacros::boot();

        // blueprint macros
        Blueprint::macro('xEnum', function (string $column, mixed $enumClass, string $comment) {
            $length = $enumClass::GetMaxLength();
            $allowed = $enumClass::Values();
            return $this->addColumn('enum', $column, compact('length', 'allowed'))->comment($enumClass::Comment($comment));
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path("project.php"),
            __DIR__ . '/docs/dist' => public_path("docs"),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/api.php');

        require_once(__DIR__ . '/helpers.php'); // register ee() helper
    }
}
