<?php

namespace SeQura\Core\BusinessLogic\DataAccess\OrderCleanup\Repositories;

use SeQura\Core\BusinessLogic\Domain\OrderCleanup\RepositoryContracts\OrderCleanupScheduleRepositoryInterface;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class OrderCleanupScheduleRepository
 *
 * Keeps the next cleanup run as a global configuration value.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\OrderCleanup\Repositories
 */
class OrderCleanupScheduleRepository implements OrderCleanupScheduleRepositoryInterface
{
    /**
     * Name of the global configuration value holding the timestamp of the next cleanup run.
     */
    public const NEXT_RUN_CONFIG_KEY = 'orderCleanupNextRun';

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getNextRun(): ?int
    {
        $nextRun = $this->configurationManager->getConfigValue(static::NEXT_RUN_CONFIG_KEY, null, false);

        return is_numeric($nextRun) ? (int)$nextRun : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setNextRun(int $nextRun): void
    {
        $this->configurationManager->saveConfigValue(static::NEXT_RUN_CONFIG_KEY, $nextRun, false);
    }
}
