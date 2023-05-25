<?php

namespace SeQura\Core\Tests\Infrastructure\Configuration;

use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;

/**
 * Class ConfigurationTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\logger
 */
class ConfigurationTest extends BaseInfrastructureTestWithServices
{
    /**
     * Tests storing and retrieving value from config service
     */
    public function testStoringValue()
    {
        $this->shopConfig->saveMinLogLevel(5);
        $this->assertEquals(5, $this->shopConfig->getMinLogLevel());
        $this->shopConfig->saveMinLogLevel(2);
        $this->assertEquals(2, $this->shopConfig->getMinLogLevel());

        $this->shopConfig->setDefaultLoggerEnabled(false);
        $this->assertFalse($this->shopConfig->isDefaultLoggerEnabled());
        $this->shopConfig->setDefaultLoggerEnabled(true);
        $this->assertTrue($this->shopConfig->isDefaultLoggerEnabled());

        $this->shopConfig->setDebugModeEnabled(false);
        $this->assertFalse($this->shopConfig->isDebugModeEnabled());
        $this->shopConfig->setDebugModeEnabled(true);
        $this->assertTrue($this->shopConfig->isDebugModeEnabled());

        $this->shopConfig->setMaxStartedTasksLimit(45);
        $this->assertEquals(45, $this->shopConfig->getMaxStartedTasksLimit());
        $this->shopConfig->setMaxStartedTasksLimit(5);
        $this->assertEquals(5, $this->shopConfig->getMaxStartedTasksLimit());
    }

    public function testGetDebugModeEnabled()
    {
        // arrange
        $this->shopConfig->setDebugModeEnabled(true);

        // act
        $status = $this->shopConfig->getDebugModeEnabled();

        // assert
        self::assertTrue($status);
    }

    public function testGetDebugModeEnabledStatusNotSet()
    {
        // act
        $status = $this->shopConfig->getDebugModeEnabled();

        // assert
        self::assertFalse($status);
    }

    /**
     * Asserts that default task runner halted flag is retrieved.
     */
    public function testDefaultTaskRunnerHaltedConfig()
    {
        $this->assertFalse($this->shopConfig->isTaskRunnerHalted());
    }

    /**
     * Asserts that task runner halted flag is properly set and retrieved.
     */
    public function testSettingTaskRunnerHaltedConfig()
    {
        $this->shopConfig->setTaskRunnerHalted(true);
        $this->assertTrue($this->shopConfig->isTaskRunnerHalted());

        $this->shopConfig->setTaskRunnerHalted(false);
        $this->assertFalse($this->shopConfig->isTaskRunnerHalted());

        $this->shopConfig->setTaskRunnerHalted(true);
        $this->assertTrue($this->shopConfig->isTaskRunnerHalted());
    }
}
