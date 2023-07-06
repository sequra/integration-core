<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum\SellingCountries;

/**
 * Class SellingCountriesResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses
 */
class SellingCountriesResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $sellingCountries = [];
        foreach (SellingCountries::SELLING_COUNTRIES as $key => $value) {
            $sellingCountries[] = [
                'code' => $key,
                'name' => $value
            ];
        }

        return $sellingCountries;
    }
}
