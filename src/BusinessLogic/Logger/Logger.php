<?php

namespace SeQura\Core\BusinessLogic\Logger;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;
use SeQura\Core\Infrastructure\Logger\Logger as InfrastructureLogger;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class Logger.
 *
 * @package SeQura\Core\BusinessLogic\Logger
 */
class Logger extends InfrastructureLogger
{
    /**
     * @param int $level
     * @param string $message
     * @param string $component
     * @param mixed[] $context
     *
     * @return void
     */
    protected function logMessage(int $level, string $message, string $component, array $context = []): void
    {
        $advancedSettings = $this->getAdvancedSettingsService()->getAdvancedSettings();

        if ((!$advancedSettings && $level <= self::WARNING) ||
            ($advancedSettings && $advancedSettings->isEnabled() && $level <= $advancedSettings->getLevel()) ||
            ($advancedSettings && !$advancedSettings->isEnabled())
        ) {
            parent::logMessage($level, $message, $component, $context);
        }
    }

    /**
     * @return AdvancedSettingsService
     */
    private function getAdvancedSettingsService(): AdvancedSettingsService
    {
        return ServiceRegister::getService(AdvancedSettingsService::class);
    }
}
