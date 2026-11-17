<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution;

use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\BusinessTaskRunner;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * @package SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution
 */
class BusinessTaskRunnerBootstrapTest extends BaseTestCase
{
    public function testRunnerIsResolvableAfterBootstrap(): void
    {
        BootstrapComponent::init();
        TestServiceRegister::registerService(StoreIntegrationServiceInterface::class, static function () {
            return new MockIntegrationStoreIntegrationService();
        });

        self::assertInstanceOf(BusinessTaskRunner::class, ServiceRegister::getService(BusinessTaskRunner::class));
    }
}
