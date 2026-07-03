<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Checkout;

use SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Requests\CheckoutInitializationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Responses\CheckoutInitializationResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\Domain\Checkout\Models\CheckoutInitializationData;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutInitializationService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CheckoutApiTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Checkout
 */
class CheckoutApiTest extends BaseTestCase
{
    /**
     * @var CheckoutInitializationService
     */
    private $checkoutInitializationService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->checkoutInitializationService = $this->createMock(CheckoutInitializationService::class);

        TestServiceRegister::registerService(
            CheckoutInitializationService::class,
            function () {
                return $this->checkoutInitializationService;
            }
        );
    }

    public function testGetInitializationDataSuccess(): void
    {
        // Arrange
        $this->checkoutInitializationService->method('getInitializationData')->willReturn(
            new CheckoutInitializationData('assets1', 'merchant1', ['i1', 'pp3'], 'scriptUri.com')
        );

        // Act
        $response = CheckoutAPI::get()->checkout('1')
            ->getInitializationData(new CheckoutInitializationRequest('ES', 'ES'));

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testGetInitializationDataToArray(): void
    {
        // Arrange
        $this->checkoutInitializationService->method('getInitializationData')->willReturn(
            new CheckoutInitializationData('assets1', 'merchant1', ['i1', 'pp3'], 'scriptUri.com', 'es-ES', 'EUR', ',', '.')
        );

        // Act
        /** @var CheckoutInitializationResponse $response */
        $response = CheckoutAPI::get()->checkout('1')
            ->getInitializationData(new CheckoutInitializationRequest('ES', 'ES'));

        // Assert
        self::assertEquals([
            'assetKey' => 'assets1',
            'merchant' => 'merchant1',
            'products' => ['i1', 'pp3'],
            'scriptUri' => 'scriptUri.com',
            'locale' => 'es-ES',
            'currency' => 'EUR',
            'decimalSeparator' => ',',
            'thousandSeparator' => '.',
        ], $response->toArray());
    }

    public function testGetInitializationDataWhenNotConfigured(): void
    {
        // Arrange
        $this->checkoutInitializationService->method('getInitializationData')->willReturn(null);

        // Act
        $response = CheckoutAPI::get()->checkout('1')
            ->getInitializationData(new CheckoutInitializationRequest('ES', 'ES'));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertSame([], $response->toArray());
    }
}
