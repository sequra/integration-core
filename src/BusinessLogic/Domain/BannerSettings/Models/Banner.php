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
    protected $imageUrl;

    /**
     * @var string
     */
    protected $displayLocation;

    /**
     * @param string $country
     * @param string $linkUrl
     * @param string $imageUrl
     * @param string $displayLocation
     */
    public function __construct(
        string $country,
        string $linkUrl,
        string $imageUrl,
        string $displayLocation
    ) {
        $this->country = $country;
        $this->linkUrl = $linkUrl;
        $this->imageUrl = $imageUrl;
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
    public function getLinkUrl(): string
    {
        return $this->linkUrl;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getDisplayLocation(): string
    {
        return $this->displayLocation;
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
