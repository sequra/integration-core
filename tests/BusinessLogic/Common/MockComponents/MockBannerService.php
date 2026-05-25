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
     * @var array<string, string>
     */
    protected $storedImages = [];

    /**
     * @var array<string, true>
     */
    protected $deletedImages = [];

    /**
     * @var array<string, array{country: string, from: string, to: string}>
     */
    protected $movedImages = [];

    /**
     * @inheritDoc
     */
    public function getBannerDisplayLocations(): array
    {
        return $this->bannerDisplayLocations;
    }

    /**
     * @param string[] $bannerDisplayLocations
     */
    public function setBannerDisplayLocations(array $bannerDisplayLocations): void
    {
        $this->bannerDisplayLocations = $bannerDisplayLocations;
    }

    /**
     * @inheritDoc
     */
    public function saveBannerImage(string $country, string $displayLocation, string $imageBase64): string
    {
        $key = $this->key($country, $displayLocation);
        $this->storedImages[$key] = $imageBase64;
        unset($this->deletedImages[$key]);

        return 'https://shop.test/banners/' . $country . '_' . $displayLocation . '.png';
    }

    /**
     * @inheritDoc
     */
    public function deleteBannerImage(string $country, string $displayLocation): void
    {
        $key = $this->key($country, $displayLocation);
        unset($this->storedImages[$key]);
        $this->deletedImages[$key] = true;
    }

    /**
     * @inheritDoc
     */
    public function changeBannerImageDisplayLocation(
        string $country,
        string $oldDisplayLocation,
        string $newDisplayLocation
    ): string {
        $oldKey = $this->key($country, $oldDisplayLocation);
        $newKey = $this->key($country, $newDisplayLocation);

        if (isset($this->storedImages[$oldKey])) {
            $this->storedImages[$newKey] = $this->storedImages[$oldKey];
            unset($this->storedImages[$oldKey]);
        }

        $this->movedImages[$newKey] = [
            'country' => $country,
            'from' => $oldDisplayLocation,
            'to' => $newDisplayLocation,
        ];

        return 'https://shop.test/banners/' . $country . '_' . $newDisplayLocation . '.png';
    }

    /**
     * @return array<string, array{country: string, from: string, to: string}>
     */
    public function getMovedImages(): array
    {
        return $this->movedImages;
    }

    /**
     * @return array<string, string>
     */
    public function getStoredImages(): array
    {
        return $this->storedImages;
    }

    /**
     * @return string[]
     */
    public function getDeletedImageKeys(): array
    {
        return array_keys($this->deletedImages);
    }

    private function key(string $country, string $displayLocation): string
    {
        return $country . '|' . $displayLocation;
    }
}
