<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Services;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;

/**
 * Class BannerSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Services
 */
class BannerSettingsService
{
    /**
     * @var BannerSettingsRepositoryInterface
     */
    protected $bannerSettingsRepository;

    /**
     * @param BannerSettingsRepositoryInterface $bannerSettingsRepository
     */
    public function __construct(
        BannerSettingsRepositoryInterface $bannerSettingsRepository
    ) {
        $this->bannerSettingsRepository = $bannerSettingsRepository;
    }

    /**
     * Retrieves banner settings.
     *
     * @return BannerSettings|null
     */
    public function getBannerSettings(): ?BannerSettings
    {
        return $this->bannerSettingsRepository->getBannerSettings();
    }

    /**
     * Sets banner settings.
     *
     * @param BannerSettings $bannerSettings
     *
     * @return void
     *
     * @throws InvalidURLException
     */
    public function setBannerSettings(BannerSettings $bannerSettings): void
    {
        foreach ($bannerSettings->getBannerConfigs() as $bannerConfig) {
            $this->assertValidUrl($bannerConfig->getLinkUrl());
            $this->assertValidUrl($bannerConfig->getImageUrl());
        }

        $this->bannerSettingsRepository->setBannerSettings($bannerSettings);
    }

    /**
     * Returns banner data
     *
     * @param string $country
     *
     * @return Banner|null
     */
    public function getBannerData(string $country): ?Banner
    {
        $bannerSettings = $this->getBannerSettings();

        if ($bannerSettings === null) {
            return null;
        }

        foreach ($bannerSettings->getBannerConfigs() as $bannerConfig) {
            if ($bannerConfig->getCountry() === $country) {
                return $bannerConfig;
            }
        }

        return null;
    }

    /**
     * Validates the URL
     *
     * @throws InvalidURLException
     */
    protected function assertValidUrl(string $url): void
    {
        if (mb_strlen($url) > 2048) {
            throw new InvalidURLException('URL is too long (max 2048 characters)');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidURLException('URL format is invalid');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidURLException('URL must use http or https');
        }
    }
}
