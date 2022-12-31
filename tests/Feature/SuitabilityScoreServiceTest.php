<?php

namespace Tests\Feature;

use App\Services\SuitabilityScoreService;
use Tests\TestCase;

class SuitabilityScoreServiceTest extends TestCase
{
    private SuitabilityScoreService $service;

    public function setUp(): void
    {
        $this->service = app(SuitabilityScoreService::class);
    }

    public function testGetSuitabilityScoreWhenEven(): void
    {
        $address = '1234 Even St, Somewhere, CA 12345';
        $driverName = 'Someone';

        $score = $this->service->getSuitabilityScore($address, $driverName);

        $this->assertEquals(6.0, $score);
    }

    public function testGetSuitabilityScoreWhenOdd(): void
    {
        $address = '1234 Odd Ave, Nowhere, CA 12345';
        $driverName = 'Someone';

        $score = $this->service->getSuitabilityScore($address, $driverName);

        $this->assertEquals(3.0, $score);
    }

    public function testGetSuitabilityScoreWhenHaveCommonFactors(): void
    {
        $address = '1234 Four Ave, Nowhere, CA 12345';
        $driverName = 'Theodore';

        $score = $this->service->getSuitabilityScore($address, $driverName);

        $this->assertEquals(9.0, $score);
    }

    public function testMaximizeScores(): void
    {
        $drivers = [
            'Yoda',
            'Grogu',
            'Mando'
        ];

        $destinations = [
            '1234 Tatooine Ave, Tatooine, CA 12345',
            '1234 Kashyyyk Ave, Kashyyyk, CA 12345',
            '1234 Coruscant Ave, Coruscant, CA 12345',
        ];

        foreach ($drivers as $driver) {
            foreach ($destinations as $destination) {
                $suitabilityScore = $this->service->getSuitabilityScore($destination, $driver);

                if (!cache()->has($destination)) {
                    $driversAndScores = [$driver => $suitabilityScore];
                } else {
                    $driversAndScores = cache()->get($destination);
                    $driversAndScores[$driver] = $suitabilityScore;
                }

                cache()->put($destination, $driversAndScores, now()->addMinute());
            }
        }

        $assignments = $this->service->maximizeScores($drivers, $destinations);

        $this->assertEquals([], $assignments);
    }
}
