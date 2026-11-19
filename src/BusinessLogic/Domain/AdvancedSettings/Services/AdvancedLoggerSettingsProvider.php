<?php

namespace SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Logger\Interfaces\LoggerSettingsProviderInterface;

/**
 * Class AdvancedLoggerSettingsProvider.
 *
 * @package SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services
 */
class AdvancedLoggerSettingsProvider implements LoggerSettingsProviderInterface
{
    /**
     * @var AdvancedSettingsService
     */
    private $advancedSettingsService;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param AdvancedSettingsService $advancedSettingsService
     * @param Configuration $configuration
     */
    public function __construct(AdvancedSettingsService $advancedSettingsService, Configuration $configuration)
    {
        $this->advancedSettingsService = $advancedSettingsService;
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function isDefaultLoggerEnabled(): ?bool
    {
        $advancedSettings = $this->advancedSettingsService->getAdvancedSettings();

        return $advancedSettings !== null
            ? $advancedSettings->isEnabled()
            : $this->configuration->isDefaultLoggerEnabled();
    }

    /**
     * @inheritDoc
     */
    public function getMinLogLevel(): ?int
    {
        $advancedSettings = $this->advancedSettingsService->getAdvancedSettings();

        return $advancedSettings !== null
            ? $advancedSettings->getLevel()
            : $this->configuration->getMinLogLevel();
    }

    /**
     * @inheritDoc
     */
    public function saveMinLogLevel(int $minLogLevel): void
    {
        $advancedSettings = $this->advancedSettingsService->getAdvancedSettings();

        if ($advancedSettings !== null) {
            $this->advancedSettingsService->setAdvancedSettings(
                new AdvancedSettings($advancedSettings->isEnabled(), $minLogLevel)
            );
        } else {
            $this->configuration->saveMinLogLevel($minLogLevel);
        }
    }
}
