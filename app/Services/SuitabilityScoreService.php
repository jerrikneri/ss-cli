<?php

namespace App\Services;

use AddressStringParser\Parser;
use Illuminate\Support\Arr;

class SuitabilityScoreService
{
    public const VOWELS = ['a', 'e', 'i', 'o', 'u'];

    public function getSuitabilityScore(string $address, string $driverName): float
    {
        $suitabilityScore = 0.0;

        $streetName = $this->getStreetName($address);

        // If the length of the shipment's destination street name is even, the base suitability score (SS) is the number of vowels in the driver's name multiplied by 1.5
        if ($this->lengthIsEven($streetName)) {
            $vowels = $this->getVowels($driverName);
            $suitabilityScore = $vowels * 1.5;
        }

        // If the length of the shipment's destination street name is odd, the base SS is the number of consonants in the driver's name multiplied by 1.
        if ($this->lengthIsOdd($streetName)) {
            $consonants = $this->getConsonants($driverName);
            $suitabilityScore = $consonants * 1.0;
        }

        // If the length of the shipment's destination street name shares any common factors (besides 1) with the length of the driver's name, the ss is increased by 50% above the base SS.
        $streetMultiplicationFactors = $this->getFactors($streetName);
        $driverNameMultiplicationFactors = $this->getFactors($driverName);

        $commonFactors = array_intersect($streetMultiplicationFactors, $driverNameMultiplicationFactors);

        $eligibleCommonFactors = array_filter($commonFactors, fn ($factor) => $factor !== 1);

        if (count($eligibleCommonFactors)) {
            $suitabilityScore = $suitabilityScore * 1.5;
        }

        return $suitabilityScore;
    }


    protected function getStreetName(string $address): string
    {
        $addressParser = new Parser();

        $parsedAddress = $addressParser->parseAddress($address);

        return Arr::get($parsedAddress, 'streetName', '');
    }

    protected function lengthIsOdd(string $string): bool
    {
        return strlen($string) % 2 === 1;
    }

    protected function lengthIsEven(string $string): bool
    {
        return strlen($string) % 2 === 0;
    }

    protected function getVowels(string $string): int
    {
        $arrayName = str_split($string);

        $vowels = array_filter($arrayName, fn ($letter) => in_array(strtolower($letter), self::VOWELS));

        return count($vowels);
    }

    protected function getConsonants(string $string): int
    {
        $arrayName = str_split($string);

        $consonants = array_filter($arrayName, fn ($letter) => $letter !== ' ' && !in_array(strtolower($letter), self::VOWELS));

        return count($consonants);
    }

    protected function getFactors(string $string): array
    {
        $factors = [];

        $length = strlen($string);

        for ($i = 1; $i <= $length / 2; $i++) {
            if ($length % $i === 0) {
                $factors[] = $i;
            }
        }

        $factors[] = $length;

        return $factors;
    }

}
