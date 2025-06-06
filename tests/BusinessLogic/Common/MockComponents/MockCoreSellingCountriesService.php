<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;

/**
 * Class MockCoreSellingCountriesService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCoreSellingCountriesService extends SellingCountriesService
{
    /**
     * @var SellingCountry[]
     */
    private static $sellingCountries = [];

    public function getSellingCountries(): array
    {
        return self::$sellingCountries;
    }

    /**
     * @param SellingCountry[] $sellingCountries
     *
     * @return void
     */
    public function setMockSellingCountries(array $sellingCountries): void
    {
        self::$sellingCountries = $sellingCountries;
    }
}
