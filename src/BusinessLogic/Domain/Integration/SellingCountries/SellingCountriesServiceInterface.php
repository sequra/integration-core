<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;

/**
 * Interface SellingCountriesServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries
 */
interface SellingCountriesServiceInterface
{
    /**
     * Return all configured selling countries of the shop system.
     *
     * @return SellingCountry[]
     */
    public function getSellingCountries(): array;
}
