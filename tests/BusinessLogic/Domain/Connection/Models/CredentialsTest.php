<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Models;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;

class CredentialsTest extends TestCase
{
    public function testIsAllowedForCaasWhenTheContractOptionsContainAllowCaas(): void
    {
        // Arrange
        $credentials = new Credentials('logeecom', 'ES', 'EUR', 'assetsKey', ['contract_options' => ['services', 'allow_caas']], 'sequra');

        // Act
        $allowed = $credentials->isAllowedForCaas();

        // Assert
        self::assertTrue($allowed);
    }

    public function testIsNotAllowedForCaasWhenTheContractOptionsLackAllowCaas(): void
    {
        // Arrange
        $credentials = new Credentials('logeecom', 'ES', 'EUR', 'assetsKey', ['contract_options' => ['services']], 'sequra');

        // Act
        $allowed = $credentials->isAllowedForCaas();

        // Assert
        self::assertFalse($allowed);
    }

    public function testIsNotAllowedForCaasWithoutContractOptions(): void
    {
        // Arrange
        $credentials = new Credentials('logeecom', 'ES', 'EUR', 'assetsKey', [], 'sequra');

        // Act
        $allowed = $credentials->isAllowedForCaas();

        // Assert
        self::assertFalse($allowed);
    }
}
