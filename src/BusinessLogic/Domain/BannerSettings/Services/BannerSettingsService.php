<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Services;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidBannerUrlException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
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
     * Persists the given banner list.
     *
     * Inputs are fully validated before any upload, so a bad input can't leave
     * partially uploaded images on the integration. Then images get uploaded or
     * relocated, the DB write follows, and the images of removed banners are
     * cleaned up last.
     *
     * If the DB write fails after one or more uploads already succeeded, fresh
     * uploads are rolled back on a best-effort basis; overwrites and relocations
     * are not reversed.
     *
     * @param BannerInput[] $bannerInputs
     *
     * @return BannerSettings
     *
     * @throws BannerImageRequiredException
     * @throws InvalidBannerUrlException
     * @throws Throwable
     */
    public function setBannerSettings(array $bannerInputs): BannerSettings
    {
        $existingBanners = $this->getExistingBanners();
        $existingByCountry = $this->indexBannersByCountry($existingBanners);

        $this->validateIncomingBanners($bannerInputs, $existingByCountry);

        $freshUploads = new FreshUploadTracker();
        try {
            $resolvedBanners = $this->resolveIncomingBanners($bannerInputs, $existingByCountry, $freshUploads);
            $persisted = new BannerSettings($resolvedBanners);
            $this->bannerSettingsRepository->setBannerSettings($persisted);
        } catch (Throwable $e) {
            $this->rollbackFreshUploads($freshUploads->all());

            throw $e;
        }

        $this->deleteRemovedBanners($existingBanners, $this->indexInputsByCountry($bannerInputs));

        return $persisted;
    }

    /**
     * Validates every incoming banner input before any image side effects are
     * performed, so that an invalid input does not leave partially-uploaded
     * or partially-deleted images behind.
     *
     * @param BannerInput[] $bannerInputs
     * @param array<string, Banner> $existingByCountry
     *
     * @throws BannerImageRequiredException
     * @throws InvalidBannerUrlException
     */
    protected function validateIncomingBanners(array $bannerInputs, array $existingByCountry): void
    {
        foreach ($bannerInputs as $input) {
            $this->assertValidUrl($input->getLinkUrl());
            $this->assertBannerHasImageSource($input, $existingByCountry);
        }
    }

    /**
     * Removes banner images currently uploaded to the integration server.
     *
     * Best-effort: a failure to remove an individual image is logged and the
     * loop continues so that a partial failure cannot abort the surrounding
     * flow (e.g. disconnect).
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
            $this->deleteBannerImage($banner->getCountry(), $banner->getDisplayLocation());
        }
    }

    /**
     * Deletes the banner settings.
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
     * Returns banner data.
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
     * Produces the persistable Banner for each input by running it through the
     * upload/reuse/relocate flow. New uploads are recorded on $freshUploads so
     * the caller can roll them back if a later step fails.
     *
     * @param BannerInput[] $bannerInputs
     * @param array<string, Banner> $existingByCountry
     * @param FreshUploadTracker $freshUploads
     *
     * @return Banner[]
     *
     * @throws InvalidBannerUrlException
     */
    protected function resolveIncomingBanners(
        array $bannerInputs,
        array $existingByCountry,
        FreshUploadTracker $freshUploads
    ): array {
        $resolved = [];
        foreach ($bannerInputs as $input) {
            $resolved[] = $this->resolveBannerImage($input, $existingByCountry, $freshUploads);
        }

        return $resolved;
    }

    /**
     * Best-effort rollback for uploads of new banners (countries that
     * had no prior record). Used when the persist step or a later upload in
     * the same batch fails, so not to leave orphan images on the integration
     * server. Overwrites and relocations are not rolled back here.
     *
     * @param array<int, array{country: string, displayLocation: string}> $freshUploadKeys
     */
    protected function rollbackFreshUploads(array $freshUploadKeys): void
    {
        foreach ($freshUploadKeys as $key) {
            $this->deleteBannerImage($key['country'], $key['displayLocation']);
        }
    }

    /**
     * Deletes images for countries that are no longer present in the incoming set.
     *
     * @param Banner[] $existingBanners
     * @param array<string, BannerInput> $incomingByCountry
     */
    protected function deleteRemovedBanners(array $existingBanners, array $incomingByCountry): void
    {
        foreach ($existingBanners as $banner) {
            if (!isset($incomingByCountry[$banner->getCountry()])) {
                $this->deleteBannerImage($banner->getCountry(), $banner->getDisplayLocation());
            }
        }
    }

    /**
     * Deletes a banner image, logging failures instead of propagating them.
     *
     * @param string $country
     * @param string $displayLocation
     */
    protected function deleteBannerImage(string $country, string $displayLocation): void
    {
        try {
            $this->bannerService->deleteBannerImage($country, $displayLocation);
        } catch (Throwable $e) {
            Logger::logError(
                'Failed to delete banner image.',
                'Core',
                [
                    new LogContextData('country', $country),
                    new LogContextData('displayLocation', $displayLocation),
                    new LogContextData('message', $e->getMessage()),
                    new LogContextData('type', \get_class($e)),
                ]
            );
        }
    }

    /**
     * Verifies whether the input carries an imageBase64 payload or already
     * has a persisted image for the country.
     *
     * @param BannerInput $input
     * @param array<string, Banner> $existingByCountry
     *
     * @throws BannerImageRequiredException
     */
    protected function assertBannerHasImageSource(BannerInput $input, array $existingByCountry): void
    {
        if ($this->hasImageBase64($input) || isset($existingByCountry[$input->getCountry()])) {
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
     * @param BannerInput $input
     * @param array<string, Banner> $existingByCountry
     * @param FreshUploadTracker $freshUploads
     *
     * @return Banner
     *
     * @throws InvalidBannerUrlException
     */
    protected function resolveBannerImage(
        BannerInput $input,
        array $existingByCountry,
        FreshUploadTracker $freshUploads
    ): Banner {
        $existing = $existingByCountry[$input->getCountry()] ?? null;

        $imageUrl = '';
        if ($this->hasImageBase64($input)) {
            $imageUrl = $this->uploadBannerImage($input, $existing, $freshUploads);
        } elseif ($existing !== null) {
            $imageUrl = $this->reuseOrRelocateImageUrl($input, $existing);
        }

        $banner = new Banner(
            $input->getCountry(),
            $input->getLinkUrl(),
            $imageUrl,
            $input->getDisplayLocation()
        );

        $this->assertValidUrl($banner->getImageUrl());

        return $banner;
    }

    /**
     * Uploads the banner image, deleting the previously stored one when the
     * display location has changed. Records the upload in $freshUploads only
     * when no prior record existed for the country, since that is the only
     * cleanly reversible case.
     *
     * @param BannerInput $input
     * @param Banner|null $existing
     * @param FreshUploadTracker $freshUploads
     *
     * @return string Public URL of the uploaded image.
     */
    protected function uploadBannerImage(
        BannerInput $input,
        ?Banner $existing,
        FreshUploadTracker $freshUploads
    ): string {
        $this->deleteImageIfLocationChanged($input, $existing);

        $url = $this->bannerService->saveBannerImage(
            $input->getCountry(),
            $input->getDisplayLocation(),
            $input->getImageBase64()
        );

        if ($existing === null) {
            $freshUploads->record($input->getCountry(), $input->getDisplayLocation());
        }

        return $url;
    }

    /**
     * Returns the existing image URL when the display location is unchanged,
     * otherwise asks the integration to relocate the image and returns the
     * new URL.
     *
     * @param BannerInput $input
     * @param Banner $existing
     *
     * @return string
     */
    protected function reuseOrRelocateImageUrl(BannerInput $input, Banner $existing): string
    {
        if ($existing->getDisplayLocation() === $input->getDisplayLocation()) {
            return $existing->getImageUrl();
        }

        return $this->bannerService->changeBannerImageDisplayLocation(
            $input->getCountry(),
            $existing->getDisplayLocation(),
            $input->getDisplayLocation()
        );
    }

    /**
     * Best-effort cleanup of the previously stored image when the input's
     * displayLocation has changed. Routed through the swallowing helper so a
     * failure to remove a stale file at the old location does not abort the
     * save — the new upload that follows is the load-bearing step.
     *
     * @param BannerInput $input
     * @param Banner|null $existing
     *
     * @return void
     */
    protected function deleteImageIfLocationChanged(BannerInput $input, ?Banner $existing): void
    {
        if ($existing === null || $existing->getDisplayLocation() === $input->getDisplayLocation()) {
            return;
        }

        $this->deleteBannerImage($existing->getCountry(), $existing->getDisplayLocation());
    }

    /**
     * Verifies whether the input came with an imageBase64 payload to upload.
     *
     * @param BannerInput $input
     *
     * @return bool
     */
    protected function hasImageBase64(BannerInput $input): bool
    {
        $base64 = $input->getImageBase64();

        return $base64 !== null && $base64 !== '';
    }

    /**
     * Builds a country-keyed map of the given persisted banners for quick lookup.
     *
     * @param Banner[] $banners
     *
     * @return array<string, Banner>
     */
    protected function indexBannersByCountry(array $banners): array
    {
        $indexed = [];
        foreach ($banners as $banner) {
            $indexed[$banner->getCountry()] = $banner;
        }

        return $indexed;
    }

    /**
     * Builds a country-keyed map of the given inputs for quick lookup.
     *
     * @param BannerInput[] $inputs
     *
     * @return array<string, BannerInput>
     */
    protected function indexInputsByCountry(array $inputs): array
    {
        $indexed = [];
        foreach ($inputs as $input) {
            $indexed[$input->getCountry()] = $input;
        }

        return $indexed;
    }

    /**
     * Validates the URL.
     *
     * @throws InvalidBannerUrlException
     */
    protected function assertValidUrl(string $url): void
    {
        if (mb_strlen($url) > 2048) {
            throw new InvalidBannerUrlException(
                new TranslatableLabel(
                    'URL is too long (max 2048 characters)',
                    'general.errors.bannerSettings.urlTooLong'
                )
            );
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidBannerUrlException(
                new TranslatableLabel(
                    'URL format is invalid',
                    'general.errors.bannerSettings.invalidUrlFormat'
                )
            );
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!\in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidBannerUrlException(
                new TranslatableLabel(
                    'URL must use http or https',
                    'general.errors.bannerSettings.invalidUrlScheme'
                )
            );
        }
    }
}
