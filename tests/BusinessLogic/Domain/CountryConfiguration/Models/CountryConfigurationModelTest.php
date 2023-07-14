<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Models;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
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

        new CountryConfiguration('test','');
    }

    public function testEmptyCountryCode(): void
    {
        $this->expectException(EmptyCountryConfigurationParameterException::class);

        new CountryConfiguration('','test');
    }

    public function testInvalidCountryCode(): void
    {
        $this->expectException(InvalidCountryCodeForConfigurationException::class);

        new CountryConfiguration('test','test');
    }

    public function testSettersAndGetters(): void
    {
        $seQuraCost = new CountryConfiguration('CO','logeecom');

        $seQuraCost->setCountryCode('ES');
        $seQuraCost->setMerchantId('logeecom2');

        self::assertEquals('ES', $seQuraCost->getCountryCode());
        self::assertEquals('logeecom2', $seQuraCost->getMerchantId());
    }
}
