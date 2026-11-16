<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution;

use SeQura\Core\BusinessLogic\TaskExecution\FileRecurringWorkLock;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class FileRecurringWorkLockTest extends BaseTestCase
{
    /**
     * @var string[]
     */
    private $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function testSecondLockIsRefusedWhileTheFirstHoldsTheSameFile(): void
    {
        $path = $this->uniquePath();
        $first = new FileRecurringWorkLock($path);
        $second = new FileRecurringWorkLock($path);

        self::assertTrue($first->tryAcquire());
        self::assertFalse($second->tryAcquire());

        $first->release();

        self::assertTrue($second->tryAcquire());
        $second->release();
    }

    public function testLocksOnDifferentFilesDoNotBlockEachOther(): void
    {
        $first = new FileRecurringWorkLock($this->uniquePath());
        $second = new FileRecurringWorkLock($this->uniquePath());

        self::assertTrue($first->tryAcquire());
        self::assertTrue($second->tryAcquire());

        $first->release();
        $second->release();
    }

    public function testDefaultPathIsSharedByInstancesWithoutAnArgument(): void
    {
        $first = new FileRecurringWorkLock();
        $second = new FileRecurringWorkLock();

        self::assertTrue($first->tryAcquire());
        self::assertFalse($second->tryAcquire());

        $first->release();
    }

    public function testRunsUnlockedAndWarnsWhenTheFileCannotBeOpened(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $lock = new FileRecurringWorkLock(sys_get_temp_dir() . '/sequra-missing-' . uniqid('', true) . '/work.lock');

        self::assertTrue($lock->tryAcquire());

        self::assertCount(1, $logger->loggedMessages);
        self::assertSame(Logger::WARNING, $logger->loggedMessages[0]->getLogLevel());
    }

    public function testReleaseWithoutAcquireDoesNothing(): void
    {
        (new FileRecurringWorkLock($this->uniquePath()))->release();

        self::assertTrue(true);
    }

    private function uniquePath(): string
    {
        $path = sys_get_temp_dir() . '/sequra-lock-test-' . uniqid('', true) . '.lock';
        $this->paths[] = $path;

        return $path;
    }
}
