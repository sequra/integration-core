<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Services;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageTooLargeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\EmptyBannerParameterException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class BannerSettingsTransformer
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Services
 */
class BannerSettingsTransformer
{
    /**
     * Sanity cap on the raw imageBase64 string length. 3 MiB of base64 covers
     * ~2.25 MiB of decoded image content, generous for a banner. Rejecting
     * larger payloads at the request boundary prevents abusive or malformed
     * inputs from reaching persistence and the integration's saveBannerImage.
     */
    public const MAX_IMAGE_BASE64_LENGTH = 3 * 1024 * 1024;

    /**
     * Converts a raw bannerConfigs array into a list of validated BannerInput
     * value objects. Required fields and image-size cap are enforced here so
     * bad inputs never reach persistence or remote integration calls.
     *
     * @param array<int, array<string, string>> $bannerConfigs
     *
     * @return BannerInput[]
     *
     * @throws BannerImageTooLargeException
     * @throws EmptyBannerParameterException
     */
    public function transform(array $bannerConfigs): array
    {
        $inputs = [];
        foreach ($bannerConfigs as $bannerConfig) {
            $country = $bannerConfig['country'] ?? '';
            $linkUrl = $bannerConfig['linkUrl'] ?? '';
            $displayLocation = $bannerConfig['displayLocation'] ?? '';
            $imageBase64 = isset($bannerConfig['imageBase64']) && $bannerConfig['imageBase64'] !== ''
                ? (string)$bannerConfig['imageBase64']
                : null;

            $this->assertBannerParameterNotEmpty('country', $country);
            $this->assertBannerParameterNotEmpty('linkUrl', $linkUrl);
            $this->assertBannerParameterNotEmpty('displayLocation', $displayLocation);

            if ($imageBase64 !== null) {
                $this->assertImageBase64WithinLimit($imageBase64);
            }

            $inputs[] = new BannerInput($country, $linkUrl, $displayLocation, $imageBase64);
        }

        return $inputs;
    }

    /**
     * Throws when a required banner field is missing or empty, identifying
     * which one in the error message.
     *
     * @throws EmptyBannerParameterException
     */
    protected function assertBannerParameterNotEmpty(string $name, string $value): void
    {
        if ($value !== '') {
            return;
        }

        throw new EmptyBannerParameterException(
            new TranslatableLabel(
                sprintf("Banner '%s' must not be empty.", $name),
                'general.errors.bannerSettings.emptyParameter'
            )
        );
    }

    /**
     * Rejects imageBase64 payloads larger than the configured cap so abusive
     * or malformed input can't flow further.
     *
     * @throws BannerImageTooLargeException
     */
    protected function assertImageBase64WithinLimit(string $base64): void
    {
        if (\strlen($base64) <= self::MAX_IMAGE_BASE64_LENGTH) {
            return;
        }

        throw new BannerImageTooLargeException(
            new TranslatableLabel(
                sprintf(
                    'Banner image base64 payload exceeds the maximum length of %d bytes.',
                    self::MAX_IMAGE_BASE64_LENGTH
                ),
                'general.errors.bannerSettings.imageTooLarge'
            )
        );
    }
}
