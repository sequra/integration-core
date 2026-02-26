<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries;

/**
 * Interface SellingCountriesServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries
 */
interface SellingCountriesServiceInterface
{
    /**
     * Return configured selling country ISO2 codes of the shop system.
     *
     * @return string[]
     */
    public function getSellingCountries(): array;
}
