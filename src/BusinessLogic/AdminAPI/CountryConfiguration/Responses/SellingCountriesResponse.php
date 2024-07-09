<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;

/**
 * Class SellingCountriesResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses
 */
class SellingCountriesResponse extends Response
{
    /**
     * @var SellingCountry[]
     */
    protected $sellingCountries;

    /**
     * @param SellingCountry[] $sellingCountries
     */
    public function __construct(array $sellingCountries)
    {
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $countries = [];

        foreach ($this->sellingCountries as $country) {
            $countries[] = [
                'code' => $country->getCode(),
                'name' => $country->getName()
            ];
        }

        return $countries;
    }
}
