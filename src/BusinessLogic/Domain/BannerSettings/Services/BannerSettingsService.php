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
        $existingBanners = $this->getExistingBanners();
        $existingByCountry = $this->indexByCountry($existingBanners);
        $incomingBanners = $bannerSettings->getBannerConfigs();

        $resolvedBanners = $this->resolveIncomingBanners($incomingBanners, $existingByCountry);
        $this->deleteRemovedBanners($existingBanners, $this->indexByCountry($incomingBanners));

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
                        new LogContextData('type', \get_class($e)),
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
     * @return Banner[]
     */
    protected function getExistingBanners(): array
    {
        $existingBannerSettings = $this->getBannerSettings();

        return $existingBannerSettings ? $existingBannerSettings->getBannerConfigs() : [];
    }

    /**
     * @param Banner[] $incomingBanners
     * @param array<string, Banner> $existingByCountry
     *
     * @return Banner[]
     *
     * @throws BannerImageRequiredException
     * @throws InvalidURLException
     */
    protected function resolveIncomingBanners(array $incomingBanners, array $existingByCountry): array
    {
        $resolved = [];
        foreach ($incomingBanners as $banner) {
            $this->assertValidUrl($banner->getLinkUrl());
            $this->assertBannerHasImageSource($banner, $existingByCountry);

            $resolved[] = $this->resolveBannerImage($banner, $existingByCountry);
        }

        return $resolved;
    }

    /**
     * Deletes images for countries that are no longer present in the incoming set.
     *
     * @param Banner[] $existingBanners
     * @param array<string, Banner> $incomingByCountry
     */
    protected function deleteRemovedBanners(array $existingBanners, array $incomingByCountry): void
    {
        foreach ($existingBanners as $banner) {
            if (!isset($incomingByCountry[$banner->getCountry()])) {
                $this->bannerService->deleteBannerImage(
                    $banner->getCountry(),
                    $banner->getDisplayLocation()
                );
            }
        }
    }

    /**
     * Verifies whether the banner contains an imageBase64 payload
     * or already has a persisted image in storage for the country.
     *
     * @param Banner $banner
     * @param array<string, Banner> $existingByCountry
     *
     * @throws BannerImageRequiredException
     */
    protected function assertBannerHasImageSource(Banner $banner, array $existingByCountry): void
    {
        if ($this->hasImageBase64($banner) || isset($existingByCountry[$banner->getCountry()])) {
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
     * Resolves the image URL for an incoming banner:
     * - If a new imageBase64 is supplied, uploads it (deleting the previous
     *   image first when the display location has changed).
     * - Otherwise reuses the previous URL if the display location is
     *   unchanged, or asks the integration to relocate the image when the
     *   display location has changed.
     *
     * @param Banner $banner
     * @param array<string, Banner> $existingByCountry
     *
     * @return Banner
     *
     * @throws InvalidURLException
     */
    protected function resolveBannerImage(Banner $banner, array $existingByCountry): Banner
    {
        $existing = $existingByCountry[$banner->getCountry()] ?? null;

        if ($this->hasImageBase64($banner)) {
            $this->uploadBannerImage($banner, $existing);
        } elseif ($existing !== null) {
            $banner->setImageUrl($this->reuseOrRelocateImageUrl($banner, $existing));
        }

        $banner->setImageBase64(null);
        $this->assertValidUrl($banner->getImageUrl());

        return $banner;
    }

    /**
     * Uploads the banner image, deleting the previously stored one when the
     * display location has changed.
     *
     * @param Banner $banner
     * @param Banner|null $existing
     */
    protected function uploadBannerImage(Banner $banner, ?Banner $existing): void
    {
        $this->deleteImageIfLocationChanged($banner, $existing);

        $banner->setImageUrl(
            $this->bannerService->saveBannerImage(
                $banner->getCountry(),
                $banner->getDisplayLocation(),
                $banner->getImageBase64()
            )
        );
    }

    /**
     * Returns the existing image URL when the display location is unchanged,
     * otherwise asks the integration to relocate the image and returns the
     * new URL.
     *
     * @param Banner $banner
     * @param Banner $existing
     *
     * @return string
     */
    protected function reuseOrRelocateImageUrl(Banner $banner, Banner $existing): string
    {
        if ($existing->getDisplayLocation() === $banner->getDisplayLocation()) {
            return $existing->getImageUrl();
        }

        return $this->bannerService->changeBannerImageDisplayLocation(
            $banner->getCountry(),
            $existing->getDisplayLocation(),
            $banner->getDisplayLocation()
        );
    }

    /**
     * @param Banner $banner
     * @param Banner|null $existing
     */
    protected function deleteImageIfLocationChanged(Banner $banner, ?Banner $existing): void
    {
        if ($existing === null || $existing->getDisplayLocation() === $banner->getDisplayLocation()) {
            return;
        }

        $this->bannerService->deleteBannerImage(
            $existing->getCountry(),
            $existing->getDisplayLocation()
        );
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
    protected function indexByCountry(array $banners): array
    {
        $indexed = [];
        foreach ($banners as $banner) {
            $indexed[$banner->getCountry()] = $banner;
        }

        return $indexed;
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
        if (!\in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidURLException(
                new TranslatableLabel(
                    'URL must use http or https',
                    'general.errors.bannerSettings.invalidUrlScheme'
                )
            );
        }
    }
}
