<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class Address
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Address extends OrderRequestDTO
{
    /**
     * @var string|null Customer's given names for delivery/invoice.
     */
    protected $givenNames;

    /**
     * @var string|null Customer's last names for delivery/invoice.
     */
    protected $surnames;

    /**
     * @var string Customer company name.
     */
    protected $company;

    /**
     * @var string Delivery/invoice address line 1.
     */
    protected $addressLine1;

    /**
     * @var string Delivery/invoice address line 2.
     */
    protected $addressLine2;

    /**
     * @var string Delivery/invoice address postal code.
     */
    protected $postalCode;

    /**
     * @var string Delivery/invoice address city.
     */
    protected $city;

    /**
     * @var string Delivery/invoice address country code.
     */
    protected $countryCode;

    /**
     * @var string|null Customer phone.
     */
    protected $phone;

    /**
     * @var string|null Customer mobile phone.
     */
    protected $mobilePhone;

    /**
     * @var string|null Customer region or state.
     */
    protected $state;

    /**
     * @var string|null Extra handling information that the customer adds to the order.
     */
    protected $extra;

    /**
     * @var string|null Customer VAT number.
     */
    protected $vatNumber;

    /**
     * @param string $company
     * @param string $addressLine1
     * @param string $addressLine2
     * @param string $postalCode
     * @param string $city
     * @param string $countryCode
     * @param string|null $givenNames
     * @param string|null $surnames
     * @param string|null $phone
     * @param string|null $mobilePhone
     * @param string|null $state
     * @param string|null $extra
     * @param string|null $vatNumber
     */
    public function __construct(
        string $company,
        string $addressLine1,
        string $addressLine2,
        string $postalCode,
        string $city,
        string $countryCode,
        string $givenNames = null,
        string $surnames = null,
        string $phone = null,
        string $mobilePhone = null,
        string $state = null,
        string $extra = null,
        string $vatNumber = null
    ) {
        $this->givenNames = $givenNames;
        $this->surnames = $surnames;
        $this->company = $company;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->countryCode = $countryCode;
        $this->phone = $phone;
        $this->mobilePhone = $mobilePhone;
        $this->state = $state;
        $this->extra = $extra;
        $this->vatNumber = $vatNumber;
    }

    /**
     * Create a new Address instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Address Returns a new Address instance.
     */
    public static function fromArray(array $data): Address
    {
        return new self(
            self::getDataValue($data, 'company'),
            self::getDataValue($data, 'address_line_1'),
            self::getDataValue($data, 'address_line_2'),
            self::getDataValue($data, 'postal_code'),
            self::getDataValue($data, 'city'),
            self::getDataValue($data, 'country_code'),
            self::getDataValue($data, 'given_names', null),
            self::getDataValue($data, 'surnames', null),
            self::getDataValue($data, 'phone', null),
            self::getDataValue($data, 'mobile_phone', null),
            self::getDataValue($data, 'state', null),
            self::getDataValue($data, 'extra', null),
            self::getDataValue($data, 'vat_number', null)
        );
    }


    /**
     * @return string|null
     */
    public function getGivenNames(): ?string
    {
        return $this->givenNames;
    }

    /**
     * @return string|null
     */
    public function getSurnames(): ?string
    {
        return $this->surnames;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
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
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }

    /**
     * @return string|null
     */
    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
