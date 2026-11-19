<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum;

/**
 * Interface SellingCountries
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum
 */
interface SellingCountries
{
    /**
     * All available selling countries.
     */
    public const SELLING_COUNTRIES = [
        'CO' => 'Colombia',
        'ES' => 'Spain',
        'FR' => 'France',
        'IT' => 'Italy',
        'PE' => 'Peru',
        'PT' => 'Portugal'
    ];
}
