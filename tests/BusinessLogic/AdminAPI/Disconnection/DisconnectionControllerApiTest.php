<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Disconnection;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests\DisconnectRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Responses\DisconnectResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDisconnectService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationDisconnectService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DisconnectionControllerApiTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\Disconnection
 */
class DisconnectionControllerApiTest extends BaseTestCase
{
    /**
     * @var MockDisconnectService $disconnectService
     */
    private $disconnectService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disconnectService = new MockDisconnectService(
            new MockIntegrationDisconnectService(),
            TestServiceRegister::getService(SendReportRepositoryInterface::class),
            ServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            ServiceRegister::getService(CredentialsRepositoryInterface::class),
            ServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            ServiceRegister::getService(DeploymentsRepositoryInterface::class),
            ServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
            ServiceRegister::getService(SeQuraOrderRepositoryInterface::class),
            ServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class),
            ServiceRegister::getService(PaymentMethodRepositoryInterface::class),
            ServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
            ServiceRegister::getService(StatisticalDataRepositoryInterface::class),
            ServiceRegister::getService(TransactionLogRepositoryInterface::class)
        );

        TestServiceRegister::registerService(DisconnectService::class, function () {
            return $this->disconnectService;
        });
    }

    /**
     * @return void
     */
    public function testIsDisconnectResponseSuccessful(): void
    {
        // Act
        /** @var DisconnectResponse $response */
        $response = AdminAPI::get()->disconnect('1')->disconnect(
            new DisconnectRequest('deployment', true)
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testDisconnectResponseToArray(): void
    {
        // Act
        /** @var DisconnectResponse $response */
        $response = AdminAPI::get()->disconnect('1')->disconnect(
            new DisconnectRequest('deployment', true)
        );

        // Assert
        self::assertEmpty($response->toArray());
    }
}
