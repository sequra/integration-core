<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;

/**
 * Class MockBannerService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockBannerService implements BannerServiceInterface
{
    /**
     * @var string[]
     */
    protected $bannerDisplayLocations = [];

    /**
     * @inheritDoc
     */
    public function getBannerDisplayLocations(): array
    {
        return $this->bannerDisplayLocations;
    }

    /**
     * @param array $bannerDisplayLocations
     */
    public function setBannerDisplayLocations(array $bannerDisplayLocations): void
    {
        $this->bannerDisplayLocations = $bannerDisplayLocations;
    }
}
