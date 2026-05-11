<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Banner
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Models
 */
class Banner extends DataTransferObject
{
    /**
     * @var string $country
     */
    protected $country;
    /**
     * @var string $linkUrl
     */
    protected $linkUrl;
    /**
     * @var string $imageUrl
     */
    protected $imageUrl;
    /**
     * @var string $displayLocation
     */
    protected $displayLocation;

    /**
     * @var string|null
     */
    protected $imageBase64;

    /**
     * @param string $country
     * @param string $linkUrl
     * @param string $imageUrl
     * @param string $displayLocation
     * @param string|null $imageBase64
     */
    public function __construct(
        string $country,
        string $linkUrl,
        string $imageUrl,
        string $displayLocation,
        ?string $imageBase64 = null
    ) {
        $this->country = $country;
        $this->linkUrl = $linkUrl;
        $this->imageUrl = $imageUrl;
        $this->displayLocation = $displayLocation;
        $this->imageBase64 = $imageBase64;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    /**
     * @param string $linkUrl
     */
    public function setLinkUrl(string $linkUrl): void
    {
        $this->linkUrl = $linkUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getDisplayLocation(): string
    {
        return $this->displayLocation;
    }

    /**
     * @param string $displayLocation
     */
    public function setDisplayLocation(string $displayLocation): void
    {
        $this->displayLocation = $displayLocation;
    }

    /**
     * @return string|null
     */
    public function getImageBase64(): ?string
    {
        return $this->imageBase64;
    }

    /**
     * @param string|null $imageBase64
     */
    public function setImageBase64(?string $imageBase64): void
    {
        $this->imageBase64 = $imageBase64;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'linkUrl' => $this->linkUrl,
            'imageUrl' => $this->imageUrl,
            'displayLocation' => $this->displayLocation,
        ];
    }
}
