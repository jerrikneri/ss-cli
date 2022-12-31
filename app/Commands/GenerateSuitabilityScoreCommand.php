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
    private const TEST_FILE_ITERATIONS = 100;

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

            $this->generateTestFiles();

            $pathToAddresses = self::TEST_ADDRESS_FILE_PATH;
            $pathToDriverNames = self::TEST_DRIVER_NAME_FILE_PATH;
        }

        $addressFileHandle = fopen($pathToAddresses, 'r');
        $driverNamesFileHandle = fopen($pathToDriverNames, 'r');

        $this->line('Generating suitability scores...');

        while (!feof($driverNamesFileHandle)) {
            $driverName = trim(fgets($driverNamesFileHandle));

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

//                if (!cache()->has($driverName)) {
//                    $addressesAndScores = [$address => $suitabilityScore];
//                } else {
//                    $addressesAndScores = cache()->get($driverName);
//                    $addressesAndScores[$address] = $suitabilityScore;
//                }

                if (!cache()->has($address)) {
                    $driversAndScores = [$driverName => $suitabilityScore];
                } else {
                    $driversAndScores = cache()->get($address);
                    $driversAndScores[$driverName] = $suitabilityScore;
                }

//                cache()->put($driverName, $addressesAndScores);
                cache()->put($address, $driversAndScores, now()->addMinute());
            }
        }

        fclose($addressFileHandle);
        fclose($driverNamesFileHandle);

        $this->line('Maximizing scores and creating assignments...');

        $assignments = $service->maximizeScores($drivers, array_unique($addresses));

        $this->renderAssignments($assignments);
    }

    private function renderAssignments(array $assignments): void
    {
        render(<<<'HTML'
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-black">Assignments</div>
            </div>
        HTML);

        foreach ($assignments as $assignment) {
            $html = <<<'HTML'
                <div class="py-1 ml-2">
                     <ul>
                        <li>
                            <span class="px-1 bg-blue-300 text-black">
                                Driver:
                            </span>
                            $driver
                            <span class="px-1 bg-orange-300 text-black">
                                Destination:
                            </span>
                            $destination
                            <span class="px-1 bg-gray-300 text-black">
                                Score:
                            </span>
                            $score
                        </li>
                    </ul>
                </div>
            HTML;

            $html = str_replace('$driver', $assignment['driver'], $html);

            $html = str_replace('$destination', $assignment['destination'], $html);

            $html = str_replace('$score', $assignment['suitabilityScore'], $html);

            render($html);
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
         $schedule->command(static::class)->dailyAt('03:00');
    }

    private function generateTestFiles(): void
    {
//        if (file_exists(self::TEST_ADDRESS_FILE_PATH) && file_exists(self::TEST_DRIVER_NAME_FILE_PATH)) {
//            $this->line('Using existing test files...');
//            return;
//        }

        $this->line('Generating test files...');

        $driverTestFileHandle = fopen(self::TEST_DRIVER_NAME_FILE_PATH, 'w');
        $faker = Factory::create();

        for ($i = 0; $i < self::TEST_FILE_ITERATIONS; $i++) {
            fwrite($driverTestFileHandle, $faker->name() . "\n");
        }

        fclose($driverTestFileHandle);

        $addressTestFileHandle = fopen(self::TEST_ADDRESS_FILE_PATH, 'w');

        for ($i = 0; $i < self::TEST_FILE_ITERATIONS; $i++) {
            $state = $faker->randomElement(['CA', 'AZ', 'CO', 'NV', 'UT']);
            $address = "$faker->buildingNumber $faker->streetName $faker->streetSuffix, $faker->city, $state $faker->postcode";
            fwrite($addressTestFileHandle, $address . "\n");
        }

        fclose($addressTestFileHandle);
    }
}
