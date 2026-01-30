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
     * @param ?int $page
     * @param ?int $limit
     * @param ?string $search
     *
     * @return string[]
     */
    public function getSellingCountries(?int $page = null, ?int $limit = null, ?string $search = null): array;
}
