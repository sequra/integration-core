<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation;

use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller\SolicitationController;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockCreateOrderRequestBuilder;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockOrderProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class SolicitationCheckoutAPITest
 *
 * @package BusinessLogic\CheckoutAPI\Solicitation
 */
class SolicitationCheckoutApiTest extends BaseTestCase
{
    /**
     * @var MockOrderProxy
     */
    private $orderProxy;
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderProxy = new MockOrderProxy();
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);

        TestServiceRegister::registerService(
            SolicitationController::class,
            function () {
                return new SolicitationController(new OrderService(
                    $this->orderProxy,
                    $this->orderRepository
                ));
            }
        );
    }

    public function testStartFreshSolicitation()
    {
        // Arrange
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult(
            $expectedSeQuraOrder
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(new MockCreateOrderRequestBuilder());

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));

        $actualSeQuraOrder = $this->orderRepository->getByOrderReference('testOrderRef');
        self::assertNotNull($actualSeQuraOrder);

        self::assertEquals($expectedSeQuraOrder, $actualSeQuraOrder);
        self::assertEquals($actualSeQuraOrder, $response->getSolicitedOrder());
        self::assertEquals([], $response->getAvailablePaymentMethods());
    }

    public function testStartAlreadyStartedSocilitation()
    {
        // Arrange
        $expectedSeQuraOrder = (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef');
        $this->orderProxy->setMockResult(
            $expectedSeQuraOrder
        );
        CheckoutAPI::get()->solicitation('test1')->solicitFor(new MockCreateOrderRequestBuilder());

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(new MockCreateOrderRequestBuilder());

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals(1, RepositoryRegistry::getRepository(SeQuraOrder::getClassName())->count());

        $actualSeQuraOrder = $this->orderRepository->getByOrderReference('testOrderRef');
        self::assertNotNull($actualSeQuraOrder);

        self::assertEquals($expectedSeQuraOrder, $actualSeQuraOrder);
        self::assertEquals($actualSeQuraOrder, $response->getSolicitedOrder());
    }

    public function testStartSocilitationFailure()
    {
        // Arrange
        $expectedException = new \InvalidArgumentException('Test exception during the request building');
        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef')
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(
            new MockCreateOrderRequestBuilder($expectedException)
        );

        // Assert
        self::assertFalse($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertStringContainsString($expectedException->getMessage(), $response->toArray()['errorMessage']);
    }

    public function testSolicitationReturnsAvailablePaymentMethodsForSolictedOrder()
    {
        // Arrange
        $expectedAvailablePaymentMethod = new SeQuraPaymentMethod(
            'i1',
            'title1',
            'longTitle1',
            new SeQuraCost(1, 2, 3, 4),
            new \DateTime(),
            new \DateTime(),
            'campaign1',
            'claim1',
            'description1',
            'icon1',
            'costDescription1',
            1234567.89,
            321
        );
        $this->orderProxy->setMockResult(
            (new MockCreateOrderRequestBuilder())->build()->toSequraOrderInstance('testOrderRef'),
            [$expectedAvailablePaymentMethod]
        );

        // Act
        $response = CheckoutAPI::get()->solicitation('test1')->solicitFor(new MockCreateOrderRequestBuilder());

        // Assert
        self::assertTrue($response->isSuccessful(), json_encode($response->toArray(), JSON_PRETTY_PRINT));
        self::assertEquals([$expectedAvailablePaymentMethod], $response->getAvailablePaymentMethods());
    }
}
