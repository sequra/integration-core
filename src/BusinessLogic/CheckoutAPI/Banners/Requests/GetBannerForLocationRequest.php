<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetBannerForLocationRequest
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Requests
 */
class GetBannerForLocationRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $displayLocation;

    /**
     * @param string $country
     * @param string $displayLocation
     */
    public function __construct(string $country, string $displayLocation)
    {
        $this->country = $country;
        $this->displayLocation = $displayLocation;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getDisplayLocation(): string
    {
        return $this->displayLocation;
    }

    /**
     * @param array<string> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'country'),
            self::getDataValue($data, 'displayLocation')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'displayLocation' => $this->displayLocation,
        ];
    }
}
