<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order\Builders;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\EventsWebhook;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockMerchantDataProvider;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class MerchantOrderRequestBuilderTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Order\Builders
 */
class MerchantOrderRequestBuilderTest extends BaseTestCase
{
    /**
     * @var MerchantOrderRequestBuilder $builder
     */
    private $builder;

    /**
     * @var ConnectionService $connectionService
     */
    private $connectionService;

    /**
     * @var CredentialsService $credentialsService
     */
    private $credentialsService;

    /**
     * @var MerchantDataProviderInterface $merchantDataProvider
     */
    private $merchantDataProvider;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->credentialsService = new MockCredentialsService(
            new MockConnectionProxy(),
            new MockCredentialsRepository(),
            new MockCountryConfigurationRepository(),
            new MockPaymentMethodRepository()
        );
        $this->connectionService = new MockConnectionService(
            new MockConnectionDataRepository(),
            $this->credentialsService,
            TestServiceRegister::getService(StoreIntegrationService::class)
        );
        $this->merchantDataProvider = new MockMerchantDataProvider();
        $this->builder = new MerchantOrderRequestBuilder(
            $this->connectionService,
            $this->credentialsService,
            $this->merchantDataProvider
        );
    }

    /**
     * @return void
     *
     * @throws CredentialsNotFoundException
     * @throws ConnectionDataNotFoundException
     * @throws InvalidUrlException
     */
    public function testBuildNoCredentials(): void
    {
        // Arrange
        $this->expectException(CredentialsNotFoundException::class);
        // Act
        $this->builder->build('ES', '1');

        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidUrlException
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testBuild(): void
    {
        // Arrange
        $this->credentialsService->setCredentials(
            new Credentials('merchantES', 'ES', 'EUR', 'assetsKey1', [], 'sequra')
        );
        $this->merchantDataProvider->setMockApprovedCallback('callback');
        $this->merchantDataProvider->setMockAbortUrl('https://abortUrl.test');
        $this->merchantDataProvider->setMockApprovedUrl('https://approveUrl.test');
        $this->merchantDataProvider->setMockEditUrl('https://editUrl.test');
        $this->merchantDataProvider->setMockEventsWebhookParameters(['id' => '1']);
        $this->merchantDataProvider->setMockRejectedCallback('callback');
        $this->merchantDataProvider->setMockPartPaymentDetailsGetter('getter');
        $this->merchantDataProvider->setMockNotifyUrl('https://notifyUrl.test');
        $this->merchantDataProvider->setMockReturnUrl('https://returnUrl.test');
        $this->merchantDataProvider->setMockOptions(null);
        $this->merchantDataProvider->setMockEventsWebhookUrl('https://eventsWebhookUrl.test');
        $this->merchantDataProvider->setMockNotificationParameters(['id' => '2']);
        $connectionData = new ConnectionData(
            'sandbox',
            'merchantES',
            'sequra',
            new AuthorizationCredentials('username', 'password')
        );
        $this->connectionService->saveConnectionData($connectionData);

        $signature = hash_hmac(
            'sha256',
            implode(
                '_',
                [
                    '1',
                    $connectionData->getMerchantId(),
                    $connectionData->getAuthorizationCredentials()->getUsername()
                ]
            ),
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        // Act
        $response = StoreContext::doWithStore('1', [$this->builder, 'build'], ['ES', '1']);

        // Assert
        $expectedMerchant = new Merchant(
            'merchantES',
            'https://notifyUrl.test',
            [
                'id' => '2',
                'storeId' => '1',
                'signature' => $signature,
            ],
            'https://returnUrl.test',
            'callback',
            'https://editUrl.test',
            'https://abortUrl.test',
            'callback',
            'getter',
            'https://approveUrl.test',
            null,
            new EventsWebhook('https://eventsWebhookUrl.test', [
                'id' => '1',
                'storeId' => '1',
                'signature' => $signature,
            ])
        );

        self::assertEquals($expectedMerchant, $response);
    }
}
