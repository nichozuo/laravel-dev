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

        $arr = array_map(function ($part) {
            return str()->of($part)->camel()->ucfirst();
        }, explode('/', $name));
        $fullName = "App\\Modules\\" . implode('\\', $arr) . "Controller";

        dump($fullName, $controllers[$fullName] ?? null);

        return 0;
    }
}

