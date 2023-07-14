<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;

/**
 * Class MockSellingCountriesService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockSellingCountriesService implements SellingCountriesServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getSellingCountries(): array
    {
        return [
            new SellingCountry('MO','Monaco'),
            new SellingCountry('CO','Colombia'),
            new SellingCountry('IT','Italy'),
            new SellingCountry('SR','Serbia'),
            new SellingCountry('FR','France'),
            new SellingCountry('RE','Test3'),
            new SellingCountry('AG','Test2'),
            new SellingCountry('VA','Test'),
            new SellingCountry('PE','Peru')
        ];
    }
}
