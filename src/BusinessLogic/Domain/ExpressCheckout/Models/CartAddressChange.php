<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class CartAddressChange
{
    /**
     * @var string
     */
    protected $givenName;

    /**
     * @var string
     */
    protected $surnames;

    /**
     * @var string
     */
    protected $addressLine1;

    /**
     * @var string|null
     */
    protected $addressLine2;

    /**
     * @var string
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string|null
     */
    protected $countryCode;

    /**
     * @var string|null
     */
    protected $regionId;

    /**
     * @var string
     */
    protected $mobilePhone;

    /**
     * @param string $givenName
     * @param string $surnames
     * @param string $addressLine1
     * @param string|null $addressLine2
     * @param string $postalCode
     * @param string $city
     * @param string|null $countryCode
     * @param string|null $regionId
     * @param string $mobilePhone
     */
    public function __construct(
        string $givenName,
        string $surnames,
        string $addressLine1,
        ?string $addressLine2,
        string $postalCode,
        string $city,
        ?string $countryCode,
        ?string $regionId,
        string $mobilePhone
    ) {
        $this->givenName = $givenName;
        $this->surnames = $surnames;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->countryCode = $countryCode;
        $this->regionId = $regionId;
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @return string
     */
    public function getGivenName(): string
    {
        return $this->givenName;
    }

    /**
     * @return string
     */
    public function getSurnames(): string
    {
        return $this->surnames;
    }

    /**
     * @return string
     */
    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    /**
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @return string|null
     */
    public function getRegionId(): ?string
    {
        return $this->regionId;
    }

    /**
     * @return string
     */
    public function getMobilePhone(): string
    {
        return $this->mobilePhone;
    }
}
