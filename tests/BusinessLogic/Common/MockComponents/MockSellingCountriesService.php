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
     * @inheritDoc
     */
    public function getSellingCountries(): array
    {
        return [ 'MO', 'CO', 'IT', 'SR', 'FR', 'RE', 'AG', 'VA', 'PE'];
    }
}
