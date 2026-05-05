<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Banner;

/**
 * Interface BannerServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Banner
 */
interface BannerServiceInterface
{
    /**
     * @return string[]
     */
    public function getBannerDisplayLocations(): array;
}
