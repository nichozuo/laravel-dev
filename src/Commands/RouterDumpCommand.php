<?php

namespace LaravelDev\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelDev\Services\RouterModelServices;

class RouterDumpCommand extends Command
{
    protected $signature = 'dr {controller}';
    protected $description = 'dump controller';

    /**
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $name = $this->argument('controller');
        $controllers = RouterModelServices::GenRoutersModels();
        dump(json_encode($controllers));

        return 0;
    }
}

