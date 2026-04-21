<?php

namespace PlinCode\LaravelFullName\Commands;

use Illuminate\Console\Command;

class LaravelFullNameCommand extends Command
{
    public $signature = 'laravel-full-name';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
