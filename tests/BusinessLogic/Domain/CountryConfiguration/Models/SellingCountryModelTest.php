<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Models;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class SellingCountryModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Models
 */
class SellingCountryModelTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $sellingCountry = new SellingCountry('CO', 'Colombia');
        $sellingCountry->setCode('ES');
        $sellingCountry->setName('Spain');

        self::assertEquals('ES', $sellingCountry->getCode());
        self::assertEquals('Spain', $sellingCountry->getName());
    }
}
