<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Models;

/**
 * Class Banner
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Models
 */
class Banner
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
     * @param string $country
     * @param string $displayLocation
     * @param string $linkUrl
     * @param string $imageUrl
     */
    public function __construct(
        string $country,
        string $displayLocation,
        string $linkUrl = '',
        string $imageUrl = ''
    ) {
        $this->country = $country;
        $this->displayLocation = $displayLocation;
        $this->linkUrl = $linkUrl;
        $this->imageUrl = $imageUrl;
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
}
