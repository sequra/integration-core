<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class CartAddress
{
    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    protected $fullName;

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
     * @var string
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
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $regionId;

    /**
     * @var string
     */
    protected $mobilePhone;

    /**
     * @param string $reference
     * @param string $fullName
     * @param string $givenName
     * @param string $surnames
     * @param string $addressLine1
     * @param string $addressLine2
     * @param string $postalCode
     * @param string $city
     * @param string $countryCode
     * @param string $regionId
     * @param string $mobilePhone
     */
    public function __construct(
        string $reference,
        string $fullName,
        string $givenName,
        string $surnames,
        string $addressLine1,
        string $addressLine2,
        string $postalCode,
        string $city,
        string $countryCode,
        string $regionId,
        string $mobilePhone
    ) {
        $this->reference = $reference;
        $this->fullName = $fullName;
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
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
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
     * @return string
     */
    public function getAddressLine2(): string
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
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getRegionId(): string
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
