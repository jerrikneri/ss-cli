<?php

namespace App\Commands;

use App\Services\SuitabilityScoreService;
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
    public function handle(SuitabilityScoreService $service)
    {
        $pathToAddresses = $this->option('pathToAddressesFile');

        $pathToDriverNames = $this->option('pathToDriverNamesFile');

        if (!file_exists($pathToAddresses) || !file_exists($pathToDriverNames)) {
            $this->line('Invalid path to file(s) provided.');
            return;
        }

        $addressFileHandle = fopen($pathToAddresses, 'r');

        $driverNamesFileHandle = fopen($pathToDriverNames, 'r');

        while (!feof($driverNamesFileHandle)) {
            $driverName = fgets($driverNamesFileHandle);

            $driverName = trim($driverName);

            if (!$driverName) {
                continue;
            }

            $this->line('Driver: ' . $driverName);

            rewind($addressFileHandle);

            while (!feof($addressFileHandle)) {
                $address = fgets($addressFileHandle);

                $address = trim($address);

                if (!$address) {
                    continue;
                }

//                $this->line('Address: ' . $address);

                $suitabilityScore = $service->getSuitabilityScore($address, $driverName);
                $this->line($driverName);
                $this->line($address);
                $this->line($suitabilityScore);

                if (!cache()->has($driverName)) {
                    $scores = [$address => $suitabilityScore];
                } else {
                    $scores = cache()->get($driverName);
                    $scores[$address] = $suitabilityScore;
                }

                cache()->put($driverName, $scores);
            }

            $this->line($driverName);
        }

        dd(cache()->has('John Smith'), dd(cache()->has('John Smith\n')), 'hey');
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
