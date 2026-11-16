<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution;

use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\BusinessTaskRunner;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * @package SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution
 */
class BusinessTaskRunnerBootstrapTest extends BaseTestCase
{
    public function testRunnerIsResolvableAfterBootstrap(): void
    {
        BootstrapComponent::init();

        self::assertInstanceOf(BusinessTaskRunner::class, ServiceRegister::getService(BusinessTaskRunner::class));
    }
}
