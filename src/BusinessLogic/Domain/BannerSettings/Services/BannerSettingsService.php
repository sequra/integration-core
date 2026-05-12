<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Services;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

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
     * @var BannerServiceInterface
     */
    protected $bannerService;

    /**
     * @param BannerSettingsRepositoryInterface $bannerSettingsRepository
     * @param BannerServiceInterface $bannerService
     */
    public function __construct(
        BannerSettingsRepositoryInterface $bannerSettingsRepository,
        BannerServiceInterface $bannerService
    ) {
        $this->bannerSettingsRepository = $bannerSettingsRepository;
        $this->bannerService = $bannerService;
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
     * @return BannerSettings
     *
     * @throws BannerImageRequiredException
     * @throws InvalidURLException
     */
    public function setBannerSettings(BannerSettings $bannerSettings): BannerSettings
    {
        $incomingBanners = $bannerSettings->getBannerConfigs();
        $existingBannerSettings = $this->getBannerSettings();
        $existingBanners = $existingBannerSettings
            ? $existingBannerSettings->getBannerConfigs()
            : [];

        $existingBannersByKey = $this->indexByKey($existingBanners);
        $incomingBannersByKey = $this->indexByKey($incomingBanners);

        $resolvedBanners = [];

        foreach ($incomingBanners as $banner) {
            $this->assertValidUrl($banner->getLinkUrl());
            $this->assertBannerHasImageSource($banner, $existingBannersByKey);

            $resolvedBanners[] = $this->resolveBannerImage($banner, $existingBannersByKey);
        }

        foreach (array_diff_key($existingBannersByKey, $incomingBannersByKey) as $bannerToRemove) {
            $this->bannerService->deleteBannerImage(
                $bannerToRemove->getCountry(),
                $bannerToRemove->getDisplayLocation()
            );
        }

        $persisted = new BannerSettings($resolvedBanners);
        $this->bannerSettingsRepository->setBannerSettings($persisted);

        return $persisted;
    }

    /**
     * Removes banner images currently uploaded to the integration server.
     *
     * @return void
     */
    public function deleteUploadedBannerImages(): void
    {
        $bannerSettings = $this->getBannerSettings();
        if ($bannerSettings === null) {
            return;
        }

        foreach ($bannerSettings->getBannerConfigs() as $banner) {
            try {
                $this->bannerService->deleteBannerImage(
                    $banner->getCountry(),
                    $banner->getDisplayLocation()
                );
            } catch (Throwable $e) {
                Logger::logError(
                    'Failed to delete uploaded banner image.',
                    'Core',
                    [
                        new LogContextData('country', $banner->getCountry()),
                        new LogContextData('displayLocation', $banner->getDisplayLocation()),
                        new LogContextData('message', $e->getMessage()),
                        new LogContextData('type', get_class($e)),
                    ]
                );
            }
        }
    }

    /**
     * Deletes the banner settings
     *
     * @return void
     */
    public function deleteBannerSettings(): void
    {
        $this->bannerSettingsRepository->deleteBannerSettings();
    }

    /**
     * Removes both the uploaded banner images and banner settings.
     *
     * @return void
     */
    public function clearBannerSettings(): void
    {
        $this->deleteUploadedBannerImages();
        $this->deleteBannerSettings();
    }

    /**
     * Returns banner data
     *
     * @param string $country
     * @param string $displayLocation
     *
     * @return Banner|null
     */
    public function getBannerData(string $country, string $displayLocation): ?Banner
    {
        $bannerSettings = $this->getBannerSettings();

        if ($bannerSettings === null) {
            return null;
        }

        foreach ($bannerSettings->getBannerConfigs() as $bannerConfig) {
            if ($bannerConfig->getCountry() === $country && $bannerConfig->getDisplayLocation() === $displayLocation) {
                return $bannerConfig;
            }
        }

        return null;
    }

    /**
     * Verifies whether the banner contains an imageBase64 payload
     * or already has a persisted image in storage.
     *
     * @param Banner $banner
     * @param array<string, Banner> $existingByKey
     *
     * @throws BannerImageRequiredException
     */
    protected function assertBannerHasImageSource(Banner $banner, array $existingByKey): void
    {
        if ($this->hasImageBase64($banner) || isset($existingByKey[$this->keyFor($banner)])) {
            return;
        }

        throw new BannerImageRequiredException(
            new TranslatableLabel(
                'A new banner must include an imageBase64.',
                'general.errors.bannerSettings.imageRequired'
            )
        );
    }

    /**
     * Uploads a new image when a base64 payload is present, otherwise reuses
     * the URL of the previously stored banner
     *
     * @param Banner $banner
     * @param array<string, Banner> $existingByKey
     *
     * @return Banner
     *
     * @throws InvalidURLException
     */
    protected function resolveBannerImage(Banner $banner, array $existingByKey): Banner
    {
        $key = $this->keyFor($banner);

        if ($this->hasImageBase64($banner)) {
            $banner->setImageUrl(
                $this->bannerService->saveBannerImage(
                    $banner->getCountry(),
                    $banner->getDisplayLocation(),
                    $banner->getImageBase64()
                )
            );
        } elseif (isset($existingByKey[$key])) {
            $banner->setImageUrl($existingByKey[$key]->getImageUrl());
        }

        $banner->setImageBase64(null);

        $this->assertValidUrl($banner->getImageUrl());

        return $banner;
    }

    /**
     * @param Banner $banner
     *
     * @return bool
     */
    protected function hasImageBase64(Banner $banner): bool
    {
        $base64 = $banner->getImageBase64();

        return $base64 !== null && $base64 !== '';
    }

    /**
     * @param Banner[] $banners
     *
     * @return array<string, Banner>
     */
    protected function indexByKey(array $banners): array
    {
        $indexed = [];
        foreach ($banners as $banner) {
            $indexed[$this->keyFor($banner)] = $banner;
        }

        return $indexed;
    }

    /**
     * @param Banner $banner
     *
     * @return string
     */
    protected function keyFor(Banner $banner): string
    {
        return $banner->getCountry() . '|' . $banner->getDisplayLocation();
    }

    /**
     * Validates the URL
     *
     * @throws InvalidURLException
     */
    protected function assertValidUrl(string $url): void
    {
        if (mb_strlen($url) > 2048) {
            throw new InvalidURLException(
                new TranslatableLabel(
                    'URL is too long (max 2048 characters)',
                    'general.errors.bannerSettings.urlTooLong'
                )
            );
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidURLException(
                new TranslatableLabel(
                    'URL format is invalid',
                    'general.errors.bannerSettings.invalidUrlFormat'
                )
            );
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidURLException(
                new TranslatableLabel(
                    'URL must use http or https',
                    'general.errors.bannerSettings.invalidUrlScheme'
                )
            );
        }
    }
}
