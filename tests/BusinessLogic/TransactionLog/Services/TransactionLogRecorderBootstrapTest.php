<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Services;

use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogRecorder;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class TransactionLogRecorderBootstrapTest extends BaseTestCase
{
    public function testRecorderIsResolvableAfterBootstrap(): void
    {
        BootstrapComponent::init();
        TestServiceRegister::registerService(StoreIntegrationServiceInterface::class, static function () {
            return new MockIntegrationStoreIntegrationService();
        });

        self::assertInstanceOf(TransactionLogRecorder::class, ServiceRegister::getService(TransactionLogRecorder::class));
    }
}
