<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Disconnect\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDeploymentsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationDisconnectService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderStatusMappingRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSendReportRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSeQuraOrderRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStatisticalDataRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockTransactionLogRepository;

/**
 * Class DisconnectServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectServiceTest extends BaseTestCase
{
    /**
     * @var DisconnectServiceInterface $integrationDisconnectService
     */
    private $integrationDisconnectService;

    /**
     * @var ConnectionDataRepositoryInterface $connectionDataRepository
     */
    private $connectionDataRepository;

    /**
     * @var CredentialsRepositoryInterface $credentialsRepository
     */
    private $credentialsRepository;

    /**
     * @var SendReportRepositoryInterface $sendReportRepository
     */
    private $sendReportRepository;

    /**
     * @var CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    private $countryConfigurationRepository;

    /**
     * @var DeploymentsRepositoryInterface $deploymentsRepository
     */
    private $deploymentsRepository;

    /**
     * @var GeneralSettingsRepositoryInterface $generalSettingsRepository
     */
    private $generalSettingsRepository;

    /**
     * @var SeQuraOrderRepositoryInterface $seQuraOrderRepository
     */
    private $seQuraOrderRepository;

    /**
     * @var OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository
     */
    private $orderStatusSettingsRepository;

    /**
     * @var PaymentMethodRepositoryInterface $paymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var WidgetSettingsRepositoryInterface $widgetSettingsRepository
     */
    private $widgetSettingsRepository;

    /**
     * @var StatisticalDataRepositoryInterface $statisticalDataRepository
     */
    private $statisticalDataRepository;

    /**
     * @var TransactionLogRepositoryInterface $transactionLogRepository
     */
    private $transactionLogRepository;

    /**
     * @var DisconnectService $service
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationDisconnectService = new MockIntegrationDisconnectService();
        $this->connectionDataRepository = new MockConnectionDataRepository();
        $this->credentialsRepository = new MockCredentialsRepository();
        $this->sendReportRepository = new MockSendReportRepository();
        $this->countryConfigurationRepository = new MockCountryConfigurationRepository();
        $this->deploymentsRepository = new MockDeploymentsRepository();
        $this->generalSettingsRepository = new MockGeneralSettingsRepository();
        $this->seQuraOrderRepository = new MockSeQuraOrderRepository();
        $this->orderStatusSettingsRepository = new MockOrderStatusMappingRepository();
        $this->paymentMethodRepository = new MockPaymentMethodRepository();
        $this->widgetSettingsRepository = new MockWidgetSettingsRepository();
        $this->statisticalDataRepository = new MockStatisticalDataRepository();
        $this->transactionLogRepository = new MockTransactionLogRepository();

        $this->service = new DisconnectService(
            $this->integrationDisconnectService,
            $this->sendReportRepository,
            $this->connectionDataRepository,
            $this->credentialsRepository,
            $this->countryConfigurationRepository,
            $this->deploymentsRepository,
            $this->generalSettingsRepository,
            $this->seQuraOrderRepository,
            $this->orderStatusSettingsRepository,
            $this->paymentMethodRepository,
            $this->widgetSettingsRepository,
            $this->statisticalDataRepository,
            $this->transactionLogRepository
        );
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws Exception
     */
    public function testDisconnectNotFull(): void
    {
        //Arrange
        $this->credentialsRepository->setCredentials([
                new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
                new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'svea'),
                new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'sequra'),
                new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea'),
            ]);

        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'sequra',
                new AuthorizationCredentials('username', 'password')
            )
        );
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'svea',
                new AuthorizationCredentials('username', 'password')
            )
        );

        $sendReport = new SendReport(true);
        $this->sendReportRepository->setSendReport($sendReport);

        $countries = [new CountryConfiguration('FR', 'merchant'), new CountryConfiguration('IT', 'merchant')];
        $this->countryConfigurationRepository->setCountryConfiguration($countries);
        $deployments = [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];
        $this->deploymentsRepository->setDeployments($deployments);

        $generalSettings = new GeneralSettings(true, null, null, null, null);
        $this->generalSettingsRepository->setGeneralSettings($generalSettings);

        $sequraOrder1 = new SequraOrder();
        $sequraOrder1->setReference('reference1');
        $sequraOrder2 = new SequraOrder();
        $sequraOrder2->setReference('reference2');
        $this->seQuraOrderRepository->setSequraOrder($sequraOrder1);
        $this->seQuraOrderRepository->setSequraOrder($sequraOrder2);

        $mapping1 = new OrderStatusMapping('approved', '1');
        $mapping2 = new OrderStatusMapping('approved', '2');

        $this->orderStatusSettingsRepository->setOrderStatusMapping([$mapping1, $mapping2]);
        $this->paymentMethodRepository->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'pp5',
                    'Paga el mes que viene',
                    'Paga el mes que viene',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new DateTime('0022-02-22T22:36:44Z'),
                    new DateTime('2222-02-22T21:02:00Z'),
                    'temporary',
                    null,
                    null,
                    '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 129 56" height="40" style="enable-background:new 0 0 129 56;" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;}</style><path d="M8.2,0h112.6c4.5,0,8.2,3.7,8.2,8.2v39.6c0,4.5-3.7,8.2-8.2,8.2H8.2C3.7,56,0,52.3,0,47.8V8.2C0,3.7,3.7,0,8.2,0z"/><g><g><path class="st0" d="M69.3,36.5c-0.7,0-1.4-0.1-2-0.3c1.3-1.5,2.2-3.4,2.7-5.4c0.7-3,0.2-6.1-1.2-8.7c-1.4-2.7-3.8-4.8-6.6-5.9c-1.5-0.6-3.1-0.9-4.8-0.9c-1.4,0-2.8,0.2-4.1,0.7c-2.9,1-5.3,2.9-6.9,5.5c-1.6,2.6-2.2,5.7-1.7,8.7c0.5,3,2,5.7,4.4,7.7c2.2,1.9,5.1,3,8,3c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.6-0.5-1.1-1.1-1.1c-1.9-0.1-3.8-0.8-5.2-2c-1.5-1.3-2.6-3.1-2.9-5.1c-0.3-2,0.1-4,1.1-5.7c1-1.7,2.7-3,4.6-3.7c0.9-0.3,1.8-0.4,2.7-0.4c1.1,0,2.1,0.2,3.1,0.6c1.9,0.7,3.4,2.1,4.4,3.9c1,1.8,1.2,3.8,0.8,5.8c-0.3,1.5-1.1,2.9-2.2,4.1c-0.7-0.7-1.3-1.6-1.8-2.6c-0.4-0.9-0.6-1.9-0.6-2.9c0-0.6-0.5-1.1-1.1-1.1h-2.1c-0.3,0-0.6,0.1-0.7,0.3c-0.2,0.2-0.4,0.5-0.4,0.8c0,1.6,0.4,3.1,1,4.6c0.6,1.5,1.6,2.9,2.8,4.1c1.2,1.2,2.6,2.1,4.2,2.8c1.5,0.6,3,0.9,4.6,1h0c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.3-0.1-0.6-0.3-0.8C69.9,36.6,69.6,36.5,69.3,36.5z"/></g><g><path class="st0" d="M21.1,29c-0.6-0.5-1.3-0.8-2-1c-0.7-0.3-1.5-0.5-2.3-0.7c-0.6-0.1-1.1-0.3-1.6-0.4c-0.5-0.1-0.9-0.3-1.3-0.5l-0.1,0c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.1-0.2-0.2-0.3-0.2c0-0.1-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2c0-0.2,0.1-0.3,0.2-0.4c0.1-0.2,0.3-0.3,0.6-0.4c0.3-0.1,0.6-0.2,0.9-0.3c0.2,0,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.4,0c0.6,0,1.1,0.2,1.6,0.4c0.3,0.2,1,0.7,1.6,1.2c0.1,0.1,0.3,0.2,0.5,0.2c0.2,0,0.3,0,0.4-0.1l2.2-1.5c0.2-0.1,0.3-0.3,0.3-0.5c0-0.2,0-0.4-0.1-0.6c-0.6-0.8-1.4-1.5-2.3-2c-1.1-0.6-2.4-0.9-3.8-1c-0.3,0-0.5,0-0.8,0c-0.6,0-1.2,0.1-1.8,0.2c-0.8,0.1-1.6,0.4-2.4,0.8c-0.7,0.3-1.4,0.9-1.9,1.5c-0.5,0.7-0.8,1.5-0.9,2.3c-0.1,0.8,0.1,1.7,0.5,2.4c0.4,0.6,0.9,1.2,1.5,1.6c0.6,0.4,1.3,0.7,2.1,1c0.9,0.3,1.6,0.5,2.4,0.7c0.4,0.1,0.9,0.2,1.4,0.4c0.4,0.1,0.7,0.3,1,0.4l0.1,0c0.2,0.1,0.4,0.3,0.5,0.5c0.1,0.2,0.1,0.3,0.1,0.6c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.4,0.3-0.6,0.4h-0.1l-0.1,0c-0.3,0.1-0.7,0.2-1.1,0.3c-0.2,0-0.5,0-0.7,0c-0.2,0-0.3,0-0.5,0c-0.8,0-1.6-0.2-2.2-0.6c-0.5-0.3-1-0.7-1.3-1.1C11.2,32.1,11,32,10.8,32c-0.2,0-0.3,0-0.4,0.1L8,33.8c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.2,0,0.4,0.2,0.6c0.7,0.9,1.6,1.6,2.6,2l0,0l0.1,0c1.3,0.6,2.7,0.9,4.1,0.9c0.3,0,0.7,0,1,0c0.6,0,1.2,0,1.8-0.1c0.9-0.1,1.8-0.4,2.6-0.7c0.8-0.4,1.5-0.9,2-1.6c0.6-0.8,0.9-1.6,0.9-2.6c0.1-0.8-0.1-1.6-0.4-2.3C22.2,30,21.7,29.4,21.1,29z"/></g><g><path class="st0" d="M112.4,20.5c-4.9,0-8.9,3.9-8.9,8.7s4,8.7,8.9,8.7c2.5,0,4-1,4.7-1.7v0.6c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1v-7.7C121.3,24.4,117.4,20.5,112.4,20.5z M112.4,24.6c2.6,0,4.7,2.1,4.7,4.6s-2.1,4.6-4.7,4.6c-2.6,0-4.7-2.1-4.7-4.6S109.8,24.6,112.4,24.6z"/></g><g><path class="st0" d="M101.3,20.5C101.3,20.5,101.3,20.5,101.3,20.5c-1.1,0-2.1,0.3-3.1,0.7c-1.1,0.4-2.1,1.1-2.9,1.9c-0.8,0.8-1.5,1.8-1.9,2.9c-0.4,1-0.6,2-0.7,3.1v7.9c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1V29c0-0.5,0.1-1,0.3-1.5c0.2-0.6,0.6-1.1,1-1.5c0.4-0.4,1-0.8,1.5-1c0.5-0.2,1-0.3,1.5-0.3c0.6,0,1.1-0.5,1.1-1.1v-2c0-0.3-0.2-0.6-0.4-0.8C101.9,20.6,101.7,20.5,101.3,20.5z"/></g><g><path class="st0" d="M88.8,20.3h-2c-0.6,0-1.1,0.5-1.1,1.1l0,8.4c-0.1,1.1-0.6,2-1.3,2.7c-0.4,0.4-0.9,0.7-1.4,0.9c-0.5,0.2-1.1,0.3-1.7,0.3s-1.1-0.1-1.7-0.3c-0.5-0.2-1-0.5-1.4-0.9c-0.7-0.7-1.1-1.6-1.3-2.7v-8.4c0-0.6-0.5-1.1-1.1-1.1h-2c-0.6,0-1.1,0.5-1.1,1.1v8.1c0,1.1,0.2,2.2,0.6,3.2c0.4,1,1,1.9,1.8,2.8c0.8,0.8,1.7,1.4,2.8,1.8c1.1,0.4,2.1,0.6,3.3,0.6c1.1,0,2.2-0.2,3.3-0.6c1-0.4,2-1,2.8-1.8c1.4-1.4,2.3-3.2,2.5-5.3l0-8.8C89.9,20.8,89.4,20.3,88.8,20.3z"/></g><g><path class="st0" d="M34.6,20.5c-0.4-0.1-0.8-0.1-1.2-0.1c-1.7,0-3.4,0.5-4.9,1.5c-1.8,1.2-3,3-3.6,5c-0.5,2.1-0.3,4.2,0.6,6.1c0.9,1.9,2.5,3.4,4.5,4.2c1.1,0.4,2.2,0.7,3.3,0.7c1,0,1.9-0.2,2.8-0.5c1.5-0.5,2.8-1.4,3.8-2.6c0.2-0.2,0.3-0.6,0.2-0.9c-0.1-0.3-0.3-0.6-0.6-0.8l-1.6-0.8c-0.2-0.1-0.4-0.1-0.5-0.1c-0.3,0-0.6,0.1-0.8,0.3c-0.5,0.5-1.2,0.9-1.8,1.1c-0.5,0.2-1,0.3-1.6,0.3c-0.6,0-1.3-0.1-1.8-0.4c-1.1-0.5-2-1.3-2.5-2.3c0,0,0-0.1-0.1-0.2h12.1c0.6,0,1.1-0.5,1.1-1.1v-0.9c0-2.1-0.8-4.1-2.2-5.7C38.7,21.8,36.7,20.8,34.6,20.5z M30.8,25.1c0.8-0.5,1.8-0.8,2.7-0.8c0.2,0,0.4,0,0.6,0c1.2,0.2,2.3,0.7,3,1.6c0.4,0.4,0.6,0.8,0.8,1.4h-9.1C29.3,26.4,30,25.6,30.8,25.1z"/></g></g></svg>',
                    '+ 0,00 €',
                    1500,
                    null
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Desde 0,00 €/mes',
                    'Desde 0,00 €/mes o en 3 plazos sin coste',
                    'part_payment',
                    new SeQuraCost(0, 0, 0, 0),
                    new DateTime('2000-02-22T21:22:00Z'),
                    new DateTime('2222-02-22T21:22:00Z'),
                    null,
                    'o en 3 plazos sin coste',
                    'Elige el plan de pago que prefieras. Solo con tu número de DNI/NIE, móvil y tarjeta.',
                    '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 129 56" height="40" style="enable-background:new 0 0 129 56;" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;}</style><path d="M8.2,0h112.6c4.5,0,8.2,3.7,8.2,8.2v39.6c0,4.5-3.7,8.2-8.2,8.2H8.2C3.7,56,0,52.3,0,47.8V8.2C0,3.7,3.7,0,8.2,0z"/><g><g><path class="st0" d="M69.3,36.5c-0.7,0-1.4-0.1-2-0.3c1.3-1.5,2.2-3.4,2.7-5.4c0.7-3,0.2-6.1-1.2-8.7c-1.4-2.7-3.8-4.8-6.6-5.9c-1.5-0.6-3.1-0.9-4.8-0.9c-1.4,0-2.8,0.2-4.1,0.7c-2.9,1-5.3,2.9-6.9,5.5c-1.6,2.6-2.2,5.7-1.7,8.7c0.5,3,2,5.7,4.4,7.7c2.2,1.9,5.1,3,8,3c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.6-0.5-1.1-1.1-1.1c-1.9-0.1-3.8-0.8-5.2-2c-1.5-1.3-2.6-3.1-2.9-5.1c-0.3-2,0.1-4,1.1-5.7c1-1.7,2.7-3,4.6-3.7c0.9-0.3,1.8-0.4,2.7-0.4c1.1,0,2.1,0.2,3.1,0.6c1.9,0.7,3.4,2.1,4.4,3.9c1,1.8,1.2,3.8,0.8,5.8c-0.3,1.5-1.1,2.9-2.2,4.1c-0.7-0.7-1.3-1.6-1.8-2.6c-0.4-0.9-0.6-1.9-0.6-2.9c0-0.6-0.5-1.1-1.1-1.1h-2.1c-0.3,0-0.6,0.1-0.7,0.3c-0.2,0.2-0.4,0.5-0.4,0.8c0,1.6,0.4,3.1,1,4.6c0.6,1.5,1.6,2.9,2.8,4.1c1.2,1.2,2.6,2.1,4.2,2.8c1.5,0.6,3,0.9,4.6,1h0c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.3-0.1-0.6-0.3-0.8C69.9,36.6,69.6,36.5,69.3,36.5z"/></g><g><path class="st0" d="M21.1,29c-0.6-0.5-1.3-0.8-2-1c-0.7-0.3-1.5-0.5-2.3-0.7c-0.6-0.1-1.1-0.3-1.6-0.4c-0.5-0.1-0.9-0.3-1.3-0.5l-0.1,0c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.1-0.2-0.2-0.3-0.2c0-0.1-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2c0-0.2,0.1-0.3,0.2-0.4c0.1-0.2,0.3-0.3,0.6-0.4c0.3-0.1,0.6-0.2,0.9-0.3c0.2,0,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.4,0c0.6,0,1.1,0.2,1.6,0.4c0.3,0.2,1,0.7,1.6,1.2c0.1,0.1,0.3,0.2,0.5,0.2c0.2,0,0.3,0,0.4-0.1l2.2-1.5c0.2-0.1,0.3-0.3,0.3-0.5c0-0.2,0-0.4-0.1-0.6c-0.6-0.8-1.4-1.5-2.3-2c-1.1-0.6-2.4-0.9-3.8-1c-0.3,0-0.5,0-0.8,0c-0.6,0-1.2,0.1-1.8,0.2c-0.8,0.1-1.6,0.4-2.4,0.8c-0.7,0.3-1.4,0.9-1.9,1.5c-0.5,0.7-0.8,1.5-0.9,2.3c-0.1,0.8,0.1,1.7,0.5,2.4c0.4,0.6,0.9,1.2,1.5,1.6c0.6,0.4,1.3,0.7,2.1,1c0.9,0.3,1.6,0.5,2.4,0.7c0.4,0.1,0.9,0.2,1.4,0.4c0.4,0.1,0.7,0.3,1,0.4l0.1,0c0.2,0.1,0.4,0.3,0.5,0.5c0.1,0.2,0.1,0.3,0.1,0.6c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.4,0.3-0.6,0.4h-0.1l-0.1,0c-0.3,0.1-0.7,0.2-1.1,0.3c-0.2,0-0.5,0-0.7,0c-0.2,0-0.3,0-0.5,0c-0.8,0-1.6-0.2-2.2-0.6c-0.5-0.3-1-0.7-1.3-1.1C11.2,32.1,11,32,10.8,32c-0.2,0-0.3,0-0.4,0.1L8,33.8c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.2,0,0.4,0.2,0.6c0.7,0.9,1.6,1.6,2.6,2l0,0l0.1,0c1.3,0.6,2.7,0.9,4.1,0.9c0.3,0,0.7,0,1,0c0.6,0,1.2,0,1.8-0.1c0.9-0.1,1.8-0.4,2.6-0.7c0.8-0.4,1.5-0.9,2-1.6c0.6-0.8,0.9-1.6,0.9-2.6c0.1-0.8-0.1-1.6-0.4-2.3C22.2,30,21.7,29.4,21.1,29z"/></g><g><path class="st0" d="M112.4,20.5c-4.9,0-8.9,3.9-8.9,8.7s4,8.7,8.9,8.7c2.5,0,4-1,4.7-1.7v0.6c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1v-7.7C121.3,24.4,117.4,20.5,112.4,20.5z M112.4,24.6c2.6,0,4.7,2.1,4.7,4.6s-2.1,4.6-4.7,4.6c-2.6,0-4.7-2.1-4.7-4.6S109.8,24.6,112.4,24.6z"/></g><g><path class="st0" d="M101.3,20.5C101.3,20.5,101.3,20.5,101.3,20.5c-1.1,0-2.1,0.3-3.1,0.7c-1.1,0.4-2.1,1.1-2.9,1.9c-0.8,0.8-1.5,1.8-1.9,2.9c-0.4,1-0.6,2-0.7,3.1v7.9c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1V29c0-0.5,0.1-1,0.3-1.5c0.2-0.6,0.6-1.1,1-1.5c0.4-0.4,1-0.8,1.5-1c0.5-0.2,1-0.3,1.5-0.3c0.6,0,1.1-0.5,1.1-1.1v-2c0-0.3-0.2-0.6-0.4-0.8C101.9,20.6,101.7,20.5,101.3,20.5z"/></g><g><path class="st0" d="M88.8,20.3h-2c-0.6,0-1.1,0.5-1.1,1.1l0,8.4c-0.1,1.1-0.6,2-1.3,2.7c-0.4,0.4-0.9,0.7-1.4,0.9c-0.5,0.2-1.1,0.3-1.7,0.3s-1.1-0.1-1.7-0.3c-0.5-0.2-1-0.5-1.4-0.9c-0.7-0.7-1.1-1.6-1.3-2.7v-8.4c0-0.6-0.5-1.1-1.1-1.1h-2c-0.6,0-1.1,0.5-1.1,1.1v8.1c0,1.1,0.2,2.2,0.6,3.2c0.4,1,1,1.9,1.8,2.8c0.8,0.8,1.7,1.4,2.8,1.8c1.1,0.4,2.1,0.6,3.3,0.6c1.1,0,2.2-0.2,3.3-0.6c1-0.4,2-1,2.8-1.8c1.4-1.4,2.3-3.2,2.5-5.3l0-8.8C89.9,20.8,89.4,20.3,88.8,20.3z"/></g><g><path class="st0" d="M34.6,20.5c-0.4-0.1-0.8-0.1-1.2-0.1c-1.7,0-3.4,0.5-4.9,1.5c-1.8,1.2-3,3-3.6,5c-0.5,2.1-0.3,4.2,0.6,6.1c0.9,1.9,2.5,3.4,4.5,4.2c1.1,0.4,2.2,0.7,3.3,0.7c1,0,1.9-0.2,2.8-0.5c1.5-0.5,2.8-1.4,3.8-2.6c0.2-0.2,0.3-0.6,0.2-0.9c-0.1-0.3-0.3-0.6-0.6-0.8l-1.6-0.8c-0.2-0.1-0.4-0.1-0.5-0.1c-0.3,0-0.6,0.1-0.8,0.3c-0.5,0.5-1.2,0.9-1.8,1.1c-0.5,0.2-1,0.3-1.6,0.3c-0.6,0-1.3-0.1-1.8-0.4c-1.1-0.5-2-1.3-2.5-2.3c0,0,0-0.1-0.1-0.2h12.1c0.6,0,1.1-0.5,1.1-1.1v-0.9c0-2.1-0.8-4.1-2.2-5.7C38.7,21.8,36.7,20.8,34.6,20.5z M30.8,25.1c0.8-0.5,1.8-0.8,2.7-0.8c0.2,0,0.4,0,0.6,0c1.2,0.2,2.3,0.7,3,1.6c0.4,0.4,0.6,0.8,0.8,1.4h-9.1C29.3,26.4,30,25.6,30.8,25.1z"/></g></g></svg>',
                    null,
                    5000,
                    null
                )
            ]
        );

        $widget = new WidgetSettings(true);
        $this->widgetSettingsRepository->setWidgetSettings($widget);

        $statisticalData = new StatisticalData(true);
        $this->statisticalDataRepository->setStatisticalData($statisticalData);

        $log = new TransactionLog();
        $this->transactionLogRepository->setTransactionLog($log);

        //Act
        $this->service->disconnect('sequra', false);

        //Assert
        self::assertCount(1, $this->connectionDataRepository->getAllConnectionSettings());
        self::assertCount(2, $this->credentialsRepository->getCredentials());
        self::assertEquals($sendReport, $this->sendReportRepository->getSendReport());
        self::assertEquals($countries, $this->countryConfigurationRepository->getCountryConfiguration());
        self::assertEquals($deployments, $this->deploymentsRepository->getDeployments());
        self::assertEquals($generalSettings, $this->generalSettingsRepository->getGeneralSettings());
        self::assertCount(2, $this->seQuraOrderRepository->getOrderBatchByShopReferences([]));
        self::assertCount(2, $this->orderStatusSettingsRepository->getOrderStatusMapping());
        self::assertCount(0, $this->paymentMethodRepository->getPaymentMethods(''));
        self::assertEquals($widget, $this->widgetSettingsRepository->getWidgetSettings());
        self::assertEquals($statisticalData, $this->statisticalDataRepository->getStatisticalData());
        self::assertEquals($log, $this->transactionLogRepository->getTransactionLog(''));
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws \Exception
     */
    public function testDisconnectFull(): void
    {
        //Arrange
        $this->credentialsRepository->setCredentials([
                new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
                new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
                new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'sequra'),
                new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'sequra'),
            ]);

        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                'sandbox',
                'merchant',
                'sequra',
                new AuthorizationCredentials('username', 'password')
            )
        );

        $sendReport = new SendReport(true);
        $this->sendReportRepository->setSendReport($sendReport);

        $countries = [new CountryConfiguration('FR', 'merchant'), new CountryConfiguration('IT', 'merchant')];
        $this->countryConfigurationRepository->setCountryConfiguration($countries);
        $deployments = [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];
        $this->deploymentsRepository->setDeployments($deployments);

        $generalSettings = new GeneralSettings(true, null, null, null, null);
        $this->generalSettingsRepository->setGeneralSettings($generalSettings);

        $sequraOrder1 = new SequraOrder();
        $sequraOrder1->setReference('reference1');
        $sequraOrder2 = new SequraOrder();
        $sequraOrder2->setReference('reference2');
        $this->seQuraOrderRepository->setSequraOrder($sequraOrder1);
        $this->seQuraOrderRepository->setSequraOrder($sequraOrder2);

        $mapping1 = new OrderStatusMapping('approved', '1');
        $mapping2 = new OrderStatusMapping('approved', '2');

        $this->orderStatusSettingsRepository->setOrderStatusMapping([$mapping1, $mapping2]);
        $this->paymentMethodRepository->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'pp5',
                    'Paga el mes que viene',
                    'Paga el mes que viene',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new DateTime('0022-02-22T22:36:44Z'),
                    new DateTime('2222-02-22T21:02:00Z'),
                    'temporary',
                    null,
                    null,
                    '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 129 56" height="40" style="enable-background:new 0 0 129 56;" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;}</style><path d="M8.2,0h112.6c4.5,0,8.2,3.7,8.2,8.2v39.6c0,4.5-3.7,8.2-8.2,8.2H8.2C3.7,56,0,52.3,0,47.8V8.2C0,3.7,3.7,0,8.2,0z"/><g><g><path class="st0" d="M69.3,36.5c-0.7,0-1.4-0.1-2-0.3c1.3-1.5,2.2-3.4,2.7-5.4c0.7-3,0.2-6.1-1.2-8.7c-1.4-2.7-3.8-4.8-6.6-5.9c-1.5-0.6-3.1-0.9-4.8-0.9c-1.4,0-2.8,0.2-4.1,0.7c-2.9,1-5.3,2.9-6.9,5.5c-1.6,2.6-2.2,5.7-1.7,8.7c0.5,3,2,5.7,4.4,7.7c2.2,1.9,5.1,3,8,3c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.6-0.5-1.1-1.1-1.1c-1.9-0.1-3.8-0.8-5.2-2c-1.5-1.3-2.6-3.1-2.9-5.1c-0.3-2,0.1-4,1.1-5.7c1-1.7,2.7-3,4.6-3.7c0.9-0.3,1.8-0.4,2.7-0.4c1.1,0,2.1,0.2,3.1,0.6c1.9,0.7,3.4,2.1,4.4,3.9c1,1.8,1.2,3.8,0.8,5.8c-0.3,1.5-1.1,2.9-2.2,4.1c-0.7-0.7-1.3-1.6-1.8-2.6c-0.4-0.9-0.6-1.9-0.6-2.9c0-0.6-0.5-1.1-1.1-1.1h-2.1c-0.3,0-0.6,0.1-0.7,0.3c-0.2,0.2-0.4,0.5-0.4,0.8c0,1.6,0.4,3.1,1,4.6c0.6,1.5,1.6,2.9,2.8,4.1c1.2,1.2,2.6,2.1,4.2,2.8c1.5,0.6,3,0.9,4.6,1h0c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.3-0.1-0.6-0.3-0.8C69.9,36.6,69.6,36.5,69.3,36.5z"/></g><g><path class="st0" d="M21.1,29c-0.6-0.5-1.3-0.8-2-1c-0.7-0.3-1.5-0.5-2.3-0.7c-0.6-0.1-1.1-0.3-1.6-0.4c-0.5-0.1-0.9-0.3-1.3-0.5l-0.1,0c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.1-0.2-0.2-0.3-0.2c0-0.1-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2c0-0.2,0.1-0.3,0.2-0.4c0.1-0.2,0.3-0.3,0.6-0.4c0.3-0.1,0.6-0.2,0.9-0.3c0.2,0,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.4,0c0.6,0,1.1,0.2,1.6,0.4c0.3,0.2,1,0.7,1.6,1.2c0.1,0.1,0.3,0.2,0.5,0.2c0.2,0,0.3,0,0.4-0.1l2.2-1.5c0.2-0.1,0.3-0.3,0.3-0.5c0-0.2,0-0.4-0.1-0.6c-0.6-0.8-1.4-1.5-2.3-2c-1.1-0.6-2.4-0.9-3.8-1c-0.3,0-0.5,0-0.8,0c-0.6,0-1.2,0.1-1.8,0.2c-0.8,0.1-1.6,0.4-2.4,0.8c-0.7,0.3-1.4,0.9-1.9,1.5c-0.5,0.7-0.8,1.5-0.9,2.3c-0.1,0.8,0.1,1.7,0.5,2.4c0.4,0.6,0.9,1.2,1.5,1.6c0.6,0.4,1.3,0.7,2.1,1c0.9,0.3,1.6,0.5,2.4,0.7c0.4,0.1,0.9,0.2,1.4,0.4c0.4,0.1,0.7,0.3,1,0.4l0.1,0c0.2,0.1,0.4,0.3,0.5,0.5c0.1,0.2,0.1,0.3,0.1,0.6c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.4,0.3-0.6,0.4h-0.1l-0.1,0c-0.3,0.1-0.7,0.2-1.1,0.3c-0.2,0-0.5,0-0.7,0c-0.2,0-0.3,0-0.5,0c-0.8,0-1.6-0.2-2.2-0.6c-0.5-0.3-1-0.7-1.3-1.1C11.2,32.1,11,32,10.8,32c-0.2,0-0.3,0-0.4,0.1L8,33.8c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.2,0,0.4,0.2,0.6c0.7,0.9,1.6,1.6,2.6,2l0,0l0.1,0c1.3,0.6,2.7,0.9,4.1,0.9c0.3,0,0.7,0,1,0c0.6,0,1.2,0,1.8-0.1c0.9-0.1,1.8-0.4,2.6-0.7c0.8-0.4,1.5-0.9,2-1.6c0.6-0.8,0.9-1.6,0.9-2.6c0.1-0.8-0.1-1.6-0.4-2.3C22.2,30,21.7,29.4,21.1,29z"/></g><g><path class="st0" d="M112.4,20.5c-4.9,0-8.9,3.9-8.9,8.7s4,8.7,8.9,8.7c2.5,0,4-1,4.7-1.7v0.6c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1v-7.7C121.3,24.4,117.4,20.5,112.4,20.5z M112.4,24.6c2.6,0,4.7,2.1,4.7,4.6s-2.1,4.6-4.7,4.6c-2.6,0-4.7-2.1-4.7-4.6S109.8,24.6,112.4,24.6z"/></g><g><path class="st0" d="M101.3,20.5C101.3,20.5,101.3,20.5,101.3,20.5c-1.1,0-2.1,0.3-3.1,0.7c-1.1,0.4-2.1,1.1-2.9,1.9c-0.8,0.8-1.5,1.8-1.9,2.9c-0.4,1-0.6,2-0.7,3.1v7.9c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1V29c0-0.5,0.1-1,0.3-1.5c0.2-0.6,0.6-1.1,1-1.5c0.4-0.4,1-0.8,1.5-1c0.5-0.2,1-0.3,1.5-0.3c0.6,0,1.1-0.5,1.1-1.1v-2c0-0.3-0.2-0.6-0.4-0.8C101.9,20.6,101.7,20.5,101.3,20.5z"/></g><g><path class="st0" d="M88.8,20.3h-2c-0.6,0-1.1,0.5-1.1,1.1l0,8.4c-0.1,1.1-0.6,2-1.3,2.7c-0.4,0.4-0.9,0.7-1.4,0.9c-0.5,0.2-1.1,0.3-1.7,0.3s-1.1-0.1-1.7-0.3c-0.5-0.2-1-0.5-1.4-0.9c-0.7-0.7-1.1-1.6-1.3-2.7v-8.4c0-0.6-0.5-1.1-1.1-1.1h-2c-0.6,0-1.1,0.5-1.1,1.1v8.1c0,1.1,0.2,2.2,0.6,3.2c0.4,1,1,1.9,1.8,2.8c0.8,0.8,1.7,1.4,2.8,1.8c1.1,0.4,2.1,0.6,3.3,0.6c1.1,0,2.2-0.2,3.3-0.6c1-0.4,2-1,2.8-1.8c1.4-1.4,2.3-3.2,2.5-5.3l0-8.8C89.9,20.8,89.4,20.3,88.8,20.3z"/></g><g><path class="st0" d="M34.6,20.5c-0.4-0.1-0.8-0.1-1.2-0.1c-1.7,0-3.4,0.5-4.9,1.5c-1.8,1.2-3,3-3.6,5c-0.5,2.1-0.3,4.2,0.6,6.1c0.9,1.9,2.5,3.4,4.5,4.2c1.1,0.4,2.2,0.7,3.3,0.7c1,0,1.9-0.2,2.8-0.5c1.5-0.5,2.8-1.4,3.8-2.6c0.2-0.2,0.3-0.6,0.2-0.9c-0.1-0.3-0.3-0.6-0.6-0.8l-1.6-0.8c-0.2-0.1-0.4-0.1-0.5-0.1c-0.3,0-0.6,0.1-0.8,0.3c-0.5,0.5-1.2,0.9-1.8,1.1c-0.5,0.2-1,0.3-1.6,0.3c-0.6,0-1.3-0.1-1.8-0.4c-1.1-0.5-2-1.3-2.5-2.3c0,0,0-0.1-0.1-0.2h12.1c0.6,0,1.1-0.5,1.1-1.1v-0.9c0-2.1-0.8-4.1-2.2-5.7C38.7,21.8,36.7,20.8,34.6,20.5z M30.8,25.1c0.8-0.5,1.8-0.8,2.7-0.8c0.2,0,0.4,0,0.6,0c1.2,0.2,2.3,0.7,3,1.6c0.4,0.4,0.6,0.8,0.8,1.4h-9.1C29.3,26.4,30,25.6,30.8,25.1z"/></g></g></svg>',
                    '+ 0,00 €',
                    1500,
                    null
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Desde 0,00 €/mes',
                    'Desde 0,00 €/mes o en 3 plazos sin coste',
                    'part_payment',
                    new SeQuraCost(0, 0, 0, 0),
                    new DateTime('2000-02-22T21:22:00Z'),
                    new DateTime('2222-02-22T21:22:00Z'),
                    null,
                    'o en 3 plazos sin coste',
                    'Elige el plan de pago que prefieras. Solo con tu número de DNI/NIE, móvil y tarjeta.',
                    '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 129 56" height="40" style="enable-background:new 0 0 129 56;" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;}</style><path d="M8.2,0h112.6c4.5,0,8.2,3.7,8.2,8.2v39.6c0,4.5-3.7,8.2-8.2,8.2H8.2C3.7,56,0,52.3,0,47.8V8.2C0,3.7,3.7,0,8.2,0z"/><g><g><path class="st0" d="M69.3,36.5c-0.7,0-1.4-0.1-2-0.3c1.3-1.5,2.2-3.4,2.7-5.4c0.7-3,0.2-6.1-1.2-8.7c-1.4-2.7-3.8-4.8-6.6-5.9c-1.5-0.6-3.1-0.9-4.8-0.9c-1.4,0-2.8,0.2-4.1,0.7c-2.9,1-5.3,2.9-6.9,5.5c-1.6,2.6-2.2,5.7-1.7,8.7c0.5,3,2,5.7,4.4,7.7c2.2,1.9,5.1,3,8,3c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.6-0.5-1.1-1.1-1.1c-1.9-0.1-3.8-0.8-5.2-2c-1.5-1.3-2.6-3.1-2.9-5.1c-0.3-2,0.1-4,1.1-5.7c1-1.7,2.7-3,4.6-3.7c0.9-0.3,1.8-0.4,2.7-0.4c1.1,0,2.1,0.2,3.1,0.6c1.9,0.7,3.4,2.1,4.4,3.9c1,1.8,1.2,3.8,0.8,5.8c-0.3,1.5-1.1,2.9-2.2,4.1c-0.7-0.7-1.3-1.6-1.8-2.6c-0.4-0.9-0.6-1.9-0.6-2.9c0-0.6-0.5-1.1-1.1-1.1h-2.1c-0.3,0-0.6,0.1-0.7,0.3c-0.2,0.2-0.4,0.5-0.4,0.8c0,1.6,0.4,3.1,1,4.6c0.6,1.5,1.6,2.9,2.8,4.1c1.2,1.2,2.6,2.1,4.2,2.8c1.5,0.6,3,0.9,4.6,1h0c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.3-0.1-0.6-0.3-0.8C69.9,36.6,69.6,36.5,69.3,36.5z"/></g><g><path class="st0" d="M21.1,29c-0.6-0.5-1.3-0.8-2-1c-0.7-0.3-1.5-0.5-2.3-0.7c-0.6-0.1-1.1-0.3-1.6-0.4c-0.5-0.1-0.9-0.3-1.3-0.5l-0.1,0c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.1-0.2-0.2-0.3-0.2c0-0.1-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2c0-0.2,0.1-0.3,0.2-0.4c0.1-0.2,0.3-0.3,0.6-0.4c0.3-0.1,0.6-0.2,0.9-0.3c0.2,0,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.4,0c0.6,0,1.1,0.2,1.6,0.4c0.3,0.2,1,0.7,1.6,1.2c0.1,0.1,0.3,0.2,0.5,0.2c0.2,0,0.3,0,0.4-0.1l2.2-1.5c0.2-0.1,0.3-0.3,0.3-0.5c0-0.2,0-0.4-0.1-0.6c-0.6-0.8-1.4-1.5-2.3-2c-1.1-0.6-2.4-0.9-3.8-1c-0.3,0-0.5,0-0.8,0c-0.6,0-1.2,0.1-1.8,0.2c-0.8,0.1-1.6,0.4-2.4,0.8c-0.7,0.3-1.4,0.9-1.9,1.5c-0.5,0.7-0.8,1.5-0.9,2.3c-0.1,0.8,0.1,1.7,0.5,2.4c0.4,0.6,0.9,1.2,1.5,1.6c0.6,0.4,1.3,0.7,2.1,1c0.9,0.3,1.6,0.5,2.4,0.7c0.4,0.1,0.9,0.2,1.4,0.4c0.4,0.1,0.7,0.3,1,0.4l0.1,0c0.2,0.1,0.4,0.3,0.5,0.5c0.1,0.2,0.1,0.3,0.1,0.6c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.4,0.3-0.6,0.4h-0.1l-0.1,0c-0.3,0.1-0.7,0.2-1.1,0.3c-0.2,0-0.5,0-0.7,0c-0.2,0-0.3,0-0.5,0c-0.8,0-1.6-0.2-2.2-0.6c-0.5-0.3-1-0.7-1.3-1.1C11.2,32.1,11,32,10.8,32c-0.2,0-0.3,0-0.4,0.1L8,33.8c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.2,0,0.4,0.2,0.6c0.7,0.9,1.6,1.6,2.6,2l0,0l0.1,0c1.3,0.6,2.7,0.9,4.1,0.9c0.3,0,0.7,0,1,0c0.6,0,1.2,0,1.8-0.1c0.9-0.1,1.8-0.4,2.6-0.7c0.8-0.4,1.5-0.9,2-1.6c0.6-0.8,0.9-1.6,0.9-2.6c0.1-0.8-0.1-1.6-0.4-2.3C22.2,30,21.7,29.4,21.1,29z"/></g><g><path class="st0" d="M112.4,20.5c-4.9,0-8.9,3.9-8.9,8.7s4,8.7,8.9,8.7c2.5,0,4-1,4.7-1.7v0.6c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1v-7.7C121.3,24.4,117.4,20.5,112.4,20.5z M112.4,24.6c2.6,0,4.7,2.1,4.7,4.6s-2.1,4.6-4.7,4.6c-2.6,0-4.7-2.1-4.7-4.6S109.8,24.6,112.4,24.6z"/></g><g><path class="st0" d="M101.3,20.5C101.3,20.5,101.3,20.5,101.3,20.5c-1.1,0-2.1,0.3-3.1,0.7c-1.1,0.4-2.1,1.1-2.9,1.9c-0.8,0.8-1.5,1.8-1.9,2.9c-0.4,1-0.6,2-0.7,3.1v7.9c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1V29c0-0.5,0.1-1,0.3-1.5c0.2-0.6,0.6-1.1,1-1.5c0.4-0.4,1-0.8,1.5-1c0.5-0.2,1-0.3,1.5-0.3c0.6,0,1.1-0.5,1.1-1.1v-2c0-0.3-0.2-0.6-0.4-0.8C101.9,20.6,101.7,20.5,101.3,20.5z"/></g><g><path class="st0" d="M88.8,20.3h-2c-0.6,0-1.1,0.5-1.1,1.1l0,8.4c-0.1,1.1-0.6,2-1.3,2.7c-0.4,0.4-0.9,0.7-1.4,0.9c-0.5,0.2-1.1,0.3-1.7,0.3s-1.1-0.1-1.7-0.3c-0.5-0.2-1-0.5-1.4-0.9c-0.7-0.7-1.1-1.6-1.3-2.7v-8.4c0-0.6-0.5-1.1-1.1-1.1h-2c-0.6,0-1.1,0.5-1.1,1.1v8.1c0,1.1,0.2,2.2,0.6,3.2c0.4,1,1,1.9,1.8,2.8c0.8,0.8,1.7,1.4,2.8,1.8c1.1,0.4,2.1,0.6,3.3,0.6c1.1,0,2.2-0.2,3.3-0.6c1-0.4,2-1,2.8-1.8c1.4-1.4,2.3-3.2,2.5-5.3l0-8.8C89.9,20.8,89.4,20.3,88.8,20.3z"/></g><g><path class="st0" d="M34.6,20.5c-0.4-0.1-0.8-0.1-1.2-0.1c-1.7,0-3.4,0.5-4.9,1.5c-1.8,1.2-3,3-3.6,5c-0.5,2.1-0.3,4.2,0.6,6.1c0.9,1.9,2.5,3.4,4.5,4.2c1.1,0.4,2.2,0.7,3.3,0.7c1,0,1.9-0.2,2.8-0.5c1.5-0.5,2.8-1.4,3.8-2.6c0.2-0.2,0.3-0.6,0.2-0.9c-0.1-0.3-0.3-0.6-0.6-0.8l-1.6-0.8c-0.2-0.1-0.4-0.1-0.5-0.1c-0.3,0-0.6,0.1-0.8,0.3c-0.5,0.5-1.2,0.9-1.8,1.1c-0.5,0.2-1,0.3-1.6,0.3c-0.6,0-1.3-0.1-1.8-0.4c-1.1-0.5-2-1.3-2.5-2.3c0,0,0-0.1-0.1-0.2h12.1c0.6,0,1.1-0.5,1.1-1.1v-0.9c0-2.1-0.8-4.1-2.2-5.7C38.7,21.8,36.7,20.8,34.6,20.5z M30.8,25.1c0.8-0.5,1.8-0.8,2.7-0.8c0.2,0,0.4,0,0.6,0c1.2,0.2,2.3,0.7,3,1.6c0.4,0.4,0.6,0.8,0.8,1.4h-9.1C29.3,26.4,30,25.6,30.8,25.1z"/></g></g></svg>',
                    null,
                    5000,
                    null
                )
            ]
        );

        $widget = new WidgetSettings(true);
        $this->widgetSettingsRepository->setWidgetSettings($widget);

        $statisticalData = new StatisticalData(true);
        $this->statisticalDataRepository->setStatisticalData($statisticalData);

        $log = new TransactionLog();
        $this->transactionLogRepository->setTransactionLog($log);

        //Act
        $this->service->disconnect('sequra', true);

        //Assert
        self::assertCount(0, $this->connectionDataRepository->getAllConnectionSettings());
        self::assertCount(0, $this->credentialsRepository->getCredentials());
        self::assertNull($this->sendReportRepository->getSendReport());
        self::assertNull($this->countryConfigurationRepository->getCountryConfiguration());
        self::assertEmpty($this->deploymentsRepository->getDeployments());
        self::assertNull($this->generalSettingsRepository->getGeneralSettings());
        self::assertCount(0, $this->seQuraOrderRepository->getOrderBatchByShopReferences([]));
        self::assertCount(0, $this->orderStatusSettingsRepository->getOrderStatusMapping());
        self::assertCount(0, $this->paymentMethodRepository->getPaymentMethods(''));
        self::assertNull($this->widgetSettingsRepository->getWidgetSettings());
        self::assertNull($this->statisticalDataRepository->getStatisticalData());
        self::assertNull($this->transactionLogRepository->getTransactionLog(''));
    }
}
