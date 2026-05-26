<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Services;

/**
 * Class FreshUploadTracker
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Services
 */
class FreshUploadTracker
{
    /**
     * @var array<int, array{country: string, displayLocation: string}>
     */
    protected $keys = [];

    /**
     * Marks an image upload as a candidate for rollback if the save fails later.
     *
     * @param string $country
     * @param string $displayLocation
     */
    public function record(string $country, string $displayLocation): void
    {
        $this->keys[] = [
            'country' => $country,
            'displayLocation' => $displayLocation,
        ];
    }

    /**
     * Returns the list of recorded uploads so the caller can delete them on rollback.
     *
     * @return array<int, array{country: string, displayLocation: string}>
     */
    public function all(): array
    {
        return $this->keys;
    }
}
