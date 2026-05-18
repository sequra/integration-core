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
     * Returns available banner display locations in integration
     *
     * @return string[]
     */
    public function getBannerDisplayLocations(): array;

    /**
     * Persists the banner image on the integration server and returns its public URL.
     *
     * @param string $country
     * @param string $displayLocation
     * @param string $imageBase64 Raw Base64-encoded image content.
     *
     * @return string Public URL of the stored image.
     */
    public function saveBannerImage(string $country, string $displayLocation, string $imageBase64): string;

    /**
     * Removes the banner image associated with the given country and display location.
     *
     * @param string $country
     * @param string $displayLocation
     *
     * @return void
     */
    public function deleteBannerImage(string $country, string $displayLocation): void;

    /**
     * Notifies the integration that the banner image for the given country
     * is moving from one display location to another.
     * The integration is responsible for relocating the underlying image
     * and returning the public URL at the new location.
     *
     * @param string $country
     * @param string $oldDisplayLocation
     * @param string $newDisplayLocation
     *
     * @return string Public URL of the image at the new display location.
     */
    public function changeBannerImageDisplayLocation(
        string $country,
        string $oldDisplayLocation,
        string $newDisplayLocation
    ): string;
}
