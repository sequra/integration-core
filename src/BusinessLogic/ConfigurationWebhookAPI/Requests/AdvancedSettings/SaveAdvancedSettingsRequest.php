<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\AdvancedSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;

/**
 * Class SaveAdvancedSettingsRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\AdvancedSettings
 */
class SaveAdvancedSettingsRequest extends ConfigurationWebhookRequest
{
    /**
     * @var bool $isEnabled
     */
    private $isEnabled;

    /**
     * @var int $level
     */
    private $level;

    /**
     * @param bool $isEnabled
     * @param int $level
     */
    public function __construct(bool $isEnabled, int $level)
    {
        $this->isEnabled = $isEnabled;
        $this->level = $level;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self($payload['isEnabled'] ?? false, $payload['level'] ?? 1);
    }

    /**
     * @return AdvancedSettings
     */
    public function transformToDomainModel(): AdvancedSettings
    {
        return new AdvancedSettings($this->isEnabled, $this->level);
    }
}
