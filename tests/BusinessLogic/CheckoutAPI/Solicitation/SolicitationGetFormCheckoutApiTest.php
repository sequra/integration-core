<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation;

use phpDocumentor\Reflection\Types\Self_;
use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller\SolicitationController;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockCreateOrderRequestBuilder;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockOrderProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockMerchantOrderBuilder;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class SolicitationGetFormCheckoutApiTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation
 */
class SolicitationGetFormCheckoutApiTest extends BaseTestCase
{
    /**
     * @var MockOrderProxy
     */
    private $orderProxy;
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var MockMerchantOrderBuilder $merchantOrderBuilder
     */
    private $merchantOrderBuilder;

    /**
     * @var OrderCreationInterface
     */
    private $shopOrderCreation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderProxy = new MockOrderProxy();
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
        $this->merchantOrderBuilder = new MockMerchantOrderBuilder(
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(MerchantDataProviderInterface::class)
        );
        $this->shopOrderCreation = TestServiceRegister::getService(OrderCreationInterface::class);

        TestServiceRegister::registerService(
            SolicitationController::class,
            function () {
                return new SolicitationController(new OrderService(
                    $this->orderProxy,
                    $this->orderRepository,
                    $this->merchantOrderBuilder,
                    $this->shopOrderCreation
                ));
            }
        );
    }

    public function testSucessfulGetOrderForm()
    {
        // Arrange
        $expectedForm = new SeQuraForm('test form content');

        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder(null, 'testCartId'))->build()->toSequraOrderInstance('testOrderRef'),
            [],
            $expectedForm
        );
        CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new MockCreateOrderRequestBuilder(null, 'testCartId')
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->getIdentificationForm('testCartId', 'pp5', null, false);

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals($expectedForm, $response->getIdentificationForm());
        self::assertEquals(
            new GetFormRequest('testOrderRef', 'pp5', null, false, 'testMerchantId'),
            $this->orderProxy->getLastGetFormRequest()
        );
    }

    public function testGetOrderFormForMissingSeQuraOrder()
    {
        // Arrange
        $expectedExceptionMessage = 'Order form could not be fetched. SeQura order could not be found for cart id (testCartId).';

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->getIdentificationForm('testCartId');

        // Assert
        self::assertFalse($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertStringContainsString($expectedExceptionMessage, $response->toArray()['errorMessage']);
    }
}
