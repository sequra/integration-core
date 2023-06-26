<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum\SellingCountries;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;

/**
 * Class CountryConfiguration
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models
 */
class CountryConfiguration
{
    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @param string $countryCode
     * @param string $merchantId
     *
     * @throws InvalidCountryCodeForConfigurationException
     * @throws EmptyCountryConfigurationParameterException
     */
    public function __construct(string $countryCode, string $merchantId)
    {
        if(empty($countryCode) || empty($merchantId)) {
            throw new EmptyCountryConfigurationParameterException();
        }

        if(!array_key_exists($countryCode, SellingCountries::SELLING_COUNTRIES)) {
            throw new InvalidCountryCodeForConfigurationException();
        }

        $this->countryCode = $countryCode;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }
}
