<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Models;

/**
 * Class BannerInput
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Models
 */
class BannerInput
{
    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $linkUrl;

    /**
     * @var string
     */
    protected $displayLocation;

    /**
     * @var string|null
     */
    protected $imageBase64;

    /**
     * @param string $country
     * @param string $linkUrl
     * @param string $displayLocation
     * @param string|null $imageBase64
     */
    public function __construct(
        string $country,
        string $linkUrl,
        string $displayLocation,
        ?string $imageBase64 = null
    ) {
        $this->country = $country;
        $this->linkUrl = $linkUrl;
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
     * @return string
     */
    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    /**
     * @return string
     */
    public function getDisplayLocation(): string
    {
        return $this->displayLocation;
    }

    /**
     * @return string|null
     */
    public function getImageBase64(): ?string
    {
        return $this->imageBase64;
    }
}
