<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class Customer
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Customer extends OrderRequestDTO
{
    /**
     * @var string|null Customer given names.
     */
    protected $givenNames;

    /**
     * @var string|null Customer surnames.
     */
    protected $surnames;

    /**
     * @var string|null Customer title and/or gender.
     */
    protected $title;

    /**
     * @var string Customer email.
     */
    protected $email;

    /**
     * @var boolean|string Is the customer logged in.
     */
    protected $loggedIn;

    /**
     * @var string Customer language code.
     */
    protected $languageCode;

    /**
     * @var string Customer ip number.
     */
    protected $ipNumber;

    /**
     * @var string Customer browser.
     */
    protected $userAgent;

    /**
     * @var string|int|null Customer reference number in the shop's database.
     */
    protected $ref;

    /**
     * @var string|null Customer date of birth in ISO-8601 format.
     */
    protected $dateOfBirth;

    /**
     * @var string|null Customer's national identity number.
     */
    protected $nin;

    /**
     * @var string|null Customer company name.
     */
    protected $company;

    /**
     * @var string|null Shopper VAT number.
     */
    protected $vatNumber;

    /**
     * @var string|null Date when this customer was added to the shop database, in ISO-8601 format.
     */
    protected $createdAt;

    /**
     * @var string|null Date when this customer was updated in the shop database, in ISO-8601 format.
     */
    protected $updatedAt;

    /**
     * @var int|null The merchant's rating of this customer. 0 to 100, inclusive, where 0 is "cannot be trusted" and
     * 100 is "very trustworthy".
     */
    protected $rating;

    /**
     * @var string|null A validation code printed in the physical DNI or NIE.
     */
    protected $ninControl;

    /**
     * @var PreviousOrder[]|null List of customer's previous orders in this shop.
     */
    protected $previousOrders;

    /**
     * @var Vehicle|null Fields describing the customer's vehicle.
     */
    protected $vehicle;

    /**
     * @param string $email
     * @param string $languageCode
     * @param string $ipNumber
     * @param string $userAgent
     * @param string|null $givenNames
     * @param string|null $surnames
     * @param string|null $title
     * @param $ref
     * @param string|null $dateOfBirth
     * @param string|null $nin
     * @param string|null $company
     * @param string|null $vatNumber
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param int|null $rating
     * @param string|null $ninControl
     * @param array|null $previousOrders
     * @param Vehicle|null $vehicle
     * @param boolean|string $loggedIn
     */
    public function __construct(
        string $email,
        string $languageCode,
        string $ipNumber,
        string $userAgent,
        string $givenNames = null,
        string $surnames = null,
        string $title = null,
        $ref = null,
        string $dateOfBirth = null,
        string $nin = null,
        string $company = null,
        string $vatNumber = null,
        string $createdAt = null,
        string $updatedAt = null,
        int $rating = null,
        string $ninControl = null,
        array $previousOrders = null,
        Vehicle $vehicle = null,
        $loggedIn = 'unknown'
    ) {
        $this->givenNames = $givenNames;
        $this->surnames = $surnames;
        $this->email = $email;
        $this->languageCode = $languageCode;
        $this->ipNumber = $ipNumber;
        $this->userAgent = $userAgent;
        $this->title = $title;
        $this->loggedIn = $loggedIn;
        $this->ref = $ref;
        $this->dateOfBirth = $dateOfBirth;
        $this->nin = $nin;
        $this->company = $company;
        $this->vatNumber = $vatNumber;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->rating = $rating;
        $this->ninControl = $ninControl;
        $this->previousOrders = $previousOrders;
        $this->vehicle = $vehicle;
    }

    /**
     * Create a new Customer instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Customer Returns a new Customer instance.
     */
    public static function fromArray(array $data): Customer
    {
        return new self(
            self::getDataValue($data, 'email'),
            self::getDataValue($data, 'language_code'),
            self::getDataValue($data, 'ip_number'),
            self::getDataValue($data, 'user_agent'),
            self::getDataValue($data, 'given_names', null),
            self::getDataValue($data, 'surnames', null),
            self::getDataValue($data, 'title', null),
            self::getDataValue($data, 'ref', null),
            self::getDataValue($data, 'date_of_birth', null),
            self::getDataValue($data, 'nin', null),
            self::getDataValue($data, 'company', null),
            self::getDataValue($data, 'vat_number', null),
            self::getDataValue($data, 'created_at', null),
            self::getDataValue($data, 'updated_at', null),
            self::getDataValue($data, 'rating', null),
            self::getDataValue($data, 'nin_control', null),
            self::getDataValue($data, 'previous_orders', null),
            self::getDataValue($data, 'vehicle', null),
            self::getDataValue($data, 'logged_in', 'unknown')
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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return bool|string
     */
    public function getLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @return string
     */
    public function getIpNumber(): string
    {
        return $this->ipNumber;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @return int|string|null
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string|null
     */
    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string|null
     */
    public function getNin(): ?string
    {
        return $this->nin;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string|null
     */
    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @return string|null
     */
    public function getNinControl(): ?string
    {
        return $this->ninControl;
    }

    /**
     * @return PreviousOrder[]|null
     */
    public function getPreviousOrders(): ?array
    {
        return $this->previousOrders;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
