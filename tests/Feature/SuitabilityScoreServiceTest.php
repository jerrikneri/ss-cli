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
}
