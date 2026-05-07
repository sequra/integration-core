<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Models;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CountryConfigurationModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Models
 */
class CountryConfigurationModelTest extends BaseTestCase
{
    public function testEmptyMerchantId(): void
    {
        $this->expectException(EmptyCountryConfigurationParameterException::class);

        new CountryConfiguration('test', '');
    }

    public function testEmptyCountryCode(): void
    {
        $this->expectException(EmptyCountryConfigurationParameterException::class);

        new CountryConfiguration('', 'test');
    }

    public function testUnsupportedCountryCode(): void
    {
        $countryConfiguration = new CountryConfiguration('MX', 'logeecom');

        self::assertEquals('MX', $countryConfiguration->getCountryCode());
        self::assertEquals('logeecom', $countryConfiguration->getMerchantId());
    }

    public function testSettersAndGetters(): void
    {
        $countryConfiguration = new CountryConfiguration('CO', 'logeecom');

        $countryConfiguration->setCountryCode('ES');
        $countryConfiguration->setMerchantId('logeecom2');

        self::assertEquals('ES', $countryConfiguration->getCountryCode());
        self::assertEquals('logeecom2', $countryConfiguration->getMerchantId());
    }
}
