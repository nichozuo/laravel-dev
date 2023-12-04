<?php

namespace LaravelDev\Commands\GenFiles;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenFilesCommand extends Command
{
    protected $name = 'gf';
    protected $description = 'Generate files';

    /**
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['key', InputArgument::REQUIRED, 'table name'],
        ];
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['migration', 'm', InputOption::VALUE_NONE, 'gen migration file'],
            ['model', 'd', InputOption::VALUE_NONE, 'The name of the model'],
            ['controller', 'c', InputOption::VALUE_NONE, 'gen controller file'],
            ['test', 't', InputOption::VALUE_NONE, 'gen test file'],
            ['enum', 'e', InputOption::VALUE_NONE, 'gen enum file'],
            ['force', 'f', InputOption::VALUE_NONE, 'force overwrite'],
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $options = $this->options();
        $key = $this->argument('key');
        $force = $options['force'] ? '-f' : '';

        if ($options['migration']) {
            Artisan::call("gm $key $force");
        } elseif ($options['model']) {
            Artisan::call("gd $key $force");
        } elseif ($options['controller']) {
            Artisan::call("gc $key $force");
        } elseif ($options['test']) {
            Artisan::call("gt $key $force");
        } elseif ($options['enum']) {
            Artisan::call("ge $key $force");
        } else {
            $this->error('Please select a file dbType to generate');
        }
    }
}