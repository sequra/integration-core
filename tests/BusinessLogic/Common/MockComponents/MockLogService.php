<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Log\LogServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Log\Model\Log;

/**
 * Class MockLogService.
 *
 * @package Common\MockComponents
 */
class MockLogService implements LogServiceInterface
{
    /**
     * @var ?Log $log
     */
    private $log;

    /**
     * @inheritDoc
     */
    public function getLog(): Log
    {
        if (!$this->log) {
            return new Log([]);
        }

        return $this->log;
    }

    /**
     * @inheritDoc
     */
    public function removeLog(): void
    {
        $this->log = null;
    }

    /**
     * @param Log $log
     */
    public function setMockLog(Log $log): void
    {
        $this->log = $log;
    }
}
