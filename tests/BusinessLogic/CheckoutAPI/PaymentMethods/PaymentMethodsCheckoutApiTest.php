<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\PaymentMethods;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests\PaymentMethodsInCategoriesRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentMethodsCheckoutApiTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\PaymentMethods
 */
class PaymentMethodsCheckoutApiTest extends BaseTestCase
{
    /**
     * @var OrderService
     */
    private $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = $this->createMock(OrderService::class);
        TestServiceRegister::registerService(OrderService::class, function () {
            return $this->orderService;
        });
    }

    public function testGetPaymentMethodsInCategoriesReturnsCategories(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')->willReturn([
            new SeQuraPaymentMethodCategory('Paga Después', 'Paga después', 'pay_later.svg', [
                $this->paymentMethod('i1', 'Paga Después')
            ])
        ]);

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            [
                'title' => 'Paga Después',
                'description' => 'Paga después',
                'icon' => 'pay_later.svg',
                'methods' => [
                    [
                        'product' => 'i1',
                        'title' => 'Paga Después',
                        'longTitle' => 'Paga Después',
                        'cost' => [
                            'setupFee' => 0,
                            'instalmentFee' => 0,
                            'downPaymentFees' => 0,
                            'instalmentTotal' => 0,
                        ],
                        'startsAt' => '2000-02-22 21:22:00',
                        'endsAt' => '2222-02-22 21:22:00',
                        'campaign' => null,
                        'claim' => null,
                        'description' => null,
                        'icon' => null,
                        'costDescription' => null,
                        'minAmount' => null,
                        'maxAmount' => null,
                    ]
                ],
            ]
        ], $response->toArray());
    }

    public function testGetPaymentMethodsInCategoriesReturnsCategoryModels(): void
    {
        // Arrange
        $category = new SeQuraPaymentMethodCategory('Paga Después', 'Paga después', 'pay_later.svg', [
            $this->paymentMethod('i1', 'Paga Después')
        ]);
        $this->orderService->method('getAvailablePaymentMethodsInCategories')->willReturn([$category]);

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        // Integrations rendering the categories themselves read the models rather than the serialized payload.
        self::assertSame([$category], $response->getPaymentMethodCategories());
    }

    public function testGetPaymentMethodsInCategoriesOnlyNeedsOrderReference(): void
    {
        // Arrange
        $this->orderService->expects(self::once())
            ->method('getAvailablePaymentMethodsInCategories')
            ->with('testOrderRef')
            ->willReturn([]);

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testGetPaymentMethodsInCategoriesNoCategories(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')->willReturn([]);

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
        self::assertFalse($response->hasAvailablePaymentMethods());
    }

    public function testGetPaymentMethodsInCategoriesCategoryWithoutMethods(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')->willReturn([
            new SeQuraPaymentMethodCategory('Paga Después', 'Paga después', null, [])
        ]);

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertNotEmpty($response->toArray());
        self::assertFalse($response->hasAvailablePaymentMethods());
    }

    public function testGetPaymentMethodsInCategoriesOrderNotFound(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')
            ->willThrowException(new OrderNotFoundException('SeQura order with reference testOrderRef is not found.'));

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertSame(404, $response->toArray()['statusCode']);
        self::assertSame('general.errors.order.notFound', $response->toArray()['errorCode']);
    }

    public function testGetPaymentMethodsInCategoriesApiFailure(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')
            ->willThrowException(new HttpRequestException('Request failed.'));

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        self::assertFalse($response->isSuccessful());
    }

    public function testHasAvailablePaymentMethodsIsNoGuardOnAFailedCall(): void
    {
        // Arrange
        $this->orderService->method('getAvailablePaymentMethodsInCategories')
            ->willThrowException(new HttpRequestException('Request failed.'));

        // Act
        $response = CheckoutAPI::get()->solicitedOrderPaymentMethods('1')
            ->getPaymentMethodsInCategories(new PaymentMethodsInCategoriesRequest('testOrderRef'));

        // Assert
        // A failed call answers with an ErrorResponse, whose __call returns the response itself for every
        // unknown method. The helper therefore reads as truthy, and says nothing until isSuccessful() passed.
        self::assertFalse($response->isSuccessful());
        self::assertSame($response, $response->hasAvailablePaymentMethods());
    }

    /**
     * @param string $product
     * @param string $title
     *
     * @return SeQuraPaymentMethod
     *
     * @throws Exception
     */
    private function paymentMethod(string $product, string $title): SeQuraPaymentMethod
    {
        return new SeQuraPaymentMethod(
            $product,
            $title,
            $title,
            'pay_later',
            new SeQuraCost(0, 0, 0, 0),
            new DateTime('2000-02-22T21:22:00Z'),
            new DateTime('2222-02-22T21:22:00Z')
        );
    }
}
