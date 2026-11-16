<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Tasks;

use Generator;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderCleanupTaskTest extends BaseTestCase
{
    public function testYieldsAKeepAliveAfterEachBatchAndFullProgressAtTheEnd(): void
    {
        $logger = $this->registerLogger();
        $this->registerCleanup(static function () {
            yield null;
            yield null;

            return 3;
        });

        $yielded = iterator_to_array((new OrderCleanupTask())->execute(), false);

        self::assertSame([null, null, 100], $yielded);
        self::assertSame(
            ['Order cleanup deleted up to 3 stale solicitations.'],
            $this->messagesAt($logger, Logger::INFO)
        );
    }

    public function testLogsASkippedCleanupWhenTheOrderStorageCannotDeleteByCondition(): void
    {
        $logger = $this->registerLogger();
        $this->registerCleanup(static function () {
            return null;
            yield;
        });

        $yielded = iterator_to_array((new OrderCleanupTask())->execute(), false);

        self::assertSame([100], $yielded);
        self::assertSame(
            ['Order cleanup skipped: the order storage cannot delete by condition.'],
            $this->messagesAt($logger, Logger::INFO)
        );
    }

    public function testLetsACleanupFailureLeaveTheJob(): void
    {
        $logger = $this->registerLogger();
        $exception = new RuntimeException('storage unavailable');
        $this->registerCleanup(static function () use ($exception) {
            throw $exception;
            yield;
        });

        try {
            iterator_to_array((new OrderCleanupTask())->execute(), false);
            self::fail('The failure should have left the job.');
        } catch (RuntimeException $thrown) {
            self::assertSame($exception, $thrown);
        }
        self::assertSame([], $this->messagesAt($logger, Logger::INFO));
    }

    public function testCarriesNoDataThroughAStoreAndRebuild(): void
    {
        self::assertSame([], (new OrderCleanupTask())->toArray());
        self::assertInstanceOf(OrderCleanupTask::class, OrderCleanupTask::fromArray([]));
    }

    private function registerLogger(): TestShopLogger
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });

        return $logger;
    }

    private function registerCleanup(callable $generatorFactory): void
    {
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('cleanup')->willReturnCallback(static function () use ($generatorFactory): Generator {
            return $generatorFactory();
        });
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });
    }

    /**
     * @return string[]
     */
    private function messagesAt(TestShopLogger $logger, int $level): array
    {
        $messages = [];
        foreach ($logger->loggedMessages as $logData) {
            if ($logData->getLogLevel() === $level) {
                $messages[] = $logData->getMessage();
            }
        }

        return $messages;
    }
}
