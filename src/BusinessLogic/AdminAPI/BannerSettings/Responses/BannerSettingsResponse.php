<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;

/**
 * Class BannerSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses
 */
class BannerSettingsResponse extends Response
{
    /**
     * @var BannerSettings
     */
    protected $bannerSettings;

    /**
     * @param BannerSettings|null $bannerSettings
     */
    public function __construct(?BannerSettings $bannerSettings)
    {
        $this->bannerSettings = $bannerSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return !$this->bannerSettings ? [] : $this->bannerSettings->toArray();
    }
}
