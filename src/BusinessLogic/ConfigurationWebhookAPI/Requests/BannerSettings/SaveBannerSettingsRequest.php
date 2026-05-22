<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageTooLargeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\EmptyBannerParameterException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsTransformer;

/**
 * Class SaveBannerSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings
 */
class SaveBannerSettingsRequest extends ConfigurationWebhookRequest
{
    /**
     * @var array<int, array<string, string>>
     */
    protected $bannerConfigs;

    /**
     * @param array<int, array<string, string>> $bannerConfigs
     */
    public function __construct(array $bannerConfigs = [])
    {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self($payload['bannerConfigs'] ?? []);
    }

    /**
     * Transforms banner settings array to domain model.
     *
     * @return BannerInput[]
     *
     * @throws BannerImageTooLargeException
     * @throws EmptyBannerParameterException
     */
    public function transformToDomainModel(): array
    {
        return (new BannerSettingsTransformer())->transform($this->bannerConfigs);
    }
}
