<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;

/**
 * Class MockSellingCountriesService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockSellingCountriesService implements SellingCountriesServiceInterface
{
    /**
     * @var string[]
     */
    private static $sellingCountries = [];

    /**
     * @inheritDoc
     */
    public function getSellingCountries(): array
    {
        return !empty(self::$sellingCountries) ? self::$sellingCountries : [
            'MO',
            'CO',
            'IT',
            'SR',
            'FR',
            'RE',
            'AG',
            'VA',
            'PE'
        ];
    }

    /**
     * @param array $sellingCountries
     *
     * @return void
     */
    public function setSellingCountries(array $sellingCountries): void
    {
        self::$sellingCountries = $sellingCountries;
    }
}
