<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;

/**
 * Class AdvancedSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings
 */
class AdvancedSettingsResponse extends Response
{
    /**
     * @var AdvancedSettings $settings
     */
    protected $settings;

    /**
     * @param ?AdvancedSettings $settings
     */
    public function __construct(?AdvancedSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return !$this->settings ? [] : $this->settings->toArray();
    }
}
