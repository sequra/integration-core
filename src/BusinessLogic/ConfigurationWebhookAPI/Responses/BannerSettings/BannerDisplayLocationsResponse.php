<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class BannerDisplayLocationsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings
 */
class BannerDisplayLocationsResponse extends Response
{
    /**
     * @var string[]
     */
    protected $bannerDisplayLocations;

    /**
     * @param string[] $bannerDisplayLocations
     */
    public function __construct(array $bannerDisplayLocations)
    {
        $this->bannerDisplayLocations = $bannerDisplayLocations;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->bannerDisplayLocations;
    }
}
