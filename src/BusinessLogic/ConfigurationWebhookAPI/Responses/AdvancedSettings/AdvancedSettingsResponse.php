<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AdvancedSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings
 */
class AdvancedSettingsResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var array{isEnabled: bool, level: int}
     */
    protected $settings;

    /**
     * @param array{isEnabled: bool, level: int} $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->settings;
    }
}
