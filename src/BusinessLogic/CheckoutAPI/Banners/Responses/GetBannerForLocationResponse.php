<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;

/**
 * Class GetBannerForLocationResponse
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Responses
 */
class GetBannerForLocationResponse extends Response
{
    /**
     * @var Banner|null
     */
    protected $banner;

    /**
     * @param Banner|null $banner
     */
    public function __construct(?Banner $banner)
    {
        $this->banner = $banner;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if ($this->banner === null) {
            return [];
        }

        return [
            'country' => $this->banner->getCountry(),
            'displayLocation' => $this->banner->getDisplayLocation(),
            'linkUrl' => $this->banner->getLinkUrl(),
            'imageUrl' => $this->banner->getImageUrl(),
        ];
    }
}
