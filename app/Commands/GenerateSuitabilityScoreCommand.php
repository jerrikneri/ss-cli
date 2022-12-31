<?php

namespace App\Commands;

use App\Services\SuitabilityScoreService;
use Faker\Factory;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};

class GenerateSuitabilityScoreCommand extends Command
{
    private const TEST_ADDRESS_FILE_PATH = './tests/files/addressTestFile.txt';
    private const TEST_DRIVER_NAME_FILE_PATH = './tests/files/driverNamesTestFile.txt';

    protected $signature = 'generate:suitabilityScore {--pathToAddressesFile=} {--pathToDriverNamesFile=}';
    protected $description = 'Generate suitability score given a list of addresses and drivers.';

    public function handle(SuitabilityScoreService $service)
    {
        $drivers = [];
        $addresses = [];

        $pathToAddresses = $this->option('pathToAddressesFile');
        $pathToDriverNames = $this->option('pathToDriverNamesFile');

        if (!file_exists($pathToAddresses) || !file_exists($pathToDriverNames)) {
            $this->line('Invalid path to file(s) provided.');
            $this->line('Generating test files...');

            $this->generateTestFiles();

            $pathToAddresses = self::TEST_ADDRESS_FILE_PATH;
            $pathToDriverNames = self::TEST_DRIVER_NAME_FILE_PATH;
        }

        $addressFileHandle = fopen($pathToAddresses, 'r');
        $driverNamesFileHandle = fopen($pathToDriverNames, 'r');

        while (!feof($driverNamesFileHandle)) {
            $driverName = fgets($driverNamesFileHandle);

            $driverName = trim($driverName);

            if (!$driverName) {
                continue;
            }

            $drivers[] = $driverName;

            rewind($addressFileHandle);

            while (!feof($addressFileHandle)) {
                $address = fgets($addressFileHandle);

                $address = trim($address);

                if (!$address) {
                    continue;
                }

                $addresses[] = $address;

                $suitabilityScore = $service->getSuitabilityScore($address, $driverName);

                if (!cache()->has($driverName)) {
                    $scores = [$address => $suitabilityScore];
                } else {
                    $scores = cache()->get($driverName);
                    $scores[$address] = $suitabilityScore;
                }

                cache()->put($driverName, $scores);
            }
        }

        fclose($addressFileHandle);
        fclose($driverNamesFileHandle);

      $assignments = $service->maximizeScores($drivers, $addresses);


        render(<<<'HTML'
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-black">Assignments</div>
            </div>
        HTML);

        foreach ($assignments as $assignment) {
            render(<<<'HTML'
            <div class="py-1 ml-2">
                 <ul>
                    <li>$assignment</li>
                </ul>
            </div>
        HTML);
        }
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

    private function generateTestFiles(): void
    {
        $driverTestFileHandle = fopen(self::TEST_DRIVER_NAME_FILE_PATH, 'w');
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            fwrite($driverTestFileHandle, $faker->name() . "\n");
        }

        fclose($driverTestFileHandle);

        $addressTestFileHandle = fopen(self::TEST_ADDRESS_FILE_PATH, 'w');

        for ($i = 0; $i < 100; $i++) {
            $state = $faker->randomElement(['CA', 'AZ', 'CO', 'NV', 'UT']);
            $address = "$faker->buildingNumber $faker->streetName $faker->streetSuffix, $faker->city, $state $faker->postcode";
            fwrite($addressTestFileHandle, $address . "\n");
        }

        fclose($addressTestFileHandle);
    }
}
