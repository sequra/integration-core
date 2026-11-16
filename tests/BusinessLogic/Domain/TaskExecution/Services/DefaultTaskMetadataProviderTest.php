<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services;

use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\DefaultTaskMetadataProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\SampleBusinessTask;

class DefaultTaskMetadataProviderTest extends BaseTestCase
{
    public function testNamesTheQueueAfterTheJobAndUsesTheCurrentStore(): void
    {
        $config = StoreContext::doWithStore('store-7', static function () {
            return (new DefaultTaskMetadataProvider())->getExecutionConfig(new SampleBusinessTask());
        });

        self::assertSame('SampleBusinessTask', $config->getQueueName());
        self::assertSame(50, $config->getPriority());
        self::assertSame('store-7', $config->getContext());
    }

    public function testQueuesTheOrderReportOnTheQueueOfTheStore(): void
    {
        $config = StoreContext::doWithStore('store-7', static function () {
            return (new DefaultTaskMetadataProvider())->getExecutionConfig(new OrderReporter(4));
        });

        self::assertSame('order-reports-store-7', $config->getQueueName());
        self::assertSame(50, $config->getPriority());
        self::assertSame('store-7', $config->getContext());
    }

    public function testQueuesTheOrderCleanupOutsideAnyStore(): void
    {
        $config = StoreContext::doWithStore('store-7', static function () {
            return (new DefaultTaskMetadataProvider())->getExecutionConfig(new OrderCleanupTask());
        });

        self::assertSame('order-cleanup', $config->getQueueName());
        self::assertSame(0, $config->getPriority());
        self::assertSame('', $config->getContext());
    }
}
