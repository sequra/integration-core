<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution\Standalone;

use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\LegacyStoredTaskUpgrader;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\StoredTaskUpgrader;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class LegacyStoredTaskUpgraderRegistrationTest extends BaseTestCase
{
    public function testBootstrapRegistersTheLegacyUpgrader(): void
    {
        BootstrapComponent::init();

        self::assertInstanceOf(LegacyStoredTaskUpgrader::class, ServiceRegister::getService(StoredTaskUpgrader::class));
    }
}
