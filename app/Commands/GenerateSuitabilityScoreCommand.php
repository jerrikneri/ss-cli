<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};

class GenerateSuitabilityScoreCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate:suitabilityScore {--pathToAddressesFile=} {--pathToDriverNamesFile=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate suitability score given a list of addresses and drivers.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pathToAddresses = $this->option('pathToAddressesFile');

        $pathToDriverNames = $this->option('pathToDriverNamesFile');

        if (!file_exists($pathToAddresses) || !file_exists($pathToDriverNames)) {
            $this->line('Invalid path to file(s) provided.');
            return;
        }

        $addressFileHandle = fopen($pathToAddresses, 'r');

        $driverNamesFileHandle = fopen($pathToDriverNames, 'r');

        $this->line('Test');

        while (!feof($addressFileHandle)) {
            $this->line(fgets($addressFileHandle));
        }

        dd(fgets($addressFileHandle), $driverNamesFileHandle);

        render(<<<'HTML'
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-black">Laravel Zero</div>
                <em class="ml-1">
                  Simplicity is the ultimate sophistication.
                </em>
            </div>
        HTML);
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
