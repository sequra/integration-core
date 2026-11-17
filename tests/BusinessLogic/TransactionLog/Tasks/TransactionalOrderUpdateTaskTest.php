<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionalOrderUpdateTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Tasks
 */
class TransactionalOrderUpdateTaskTest extends BaseTestCase
{
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TestHttpClient
     */
    public $httpClient;

    /**
     * @var TransactionalOrderUpdateTask
     */
    private $task;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);

        $orderUpdateData = MockOrderUpdateData::getOrderUpdateData();
        $transactionData = TransactionData::fromArray([
            'merchantReference' => 'ZXCV1234',
            'eventCode' => 'ship',
            'timestamp' => 123,
            'reason' => 'Update order',
            'isSuccessful' => true,
        ]);

        $this->task = new TransactionalOrderUpdateTask($orderUpdateData, $transactionData);
    }

    /**
     * @throws Exception
     */
    public function testSuccessfulTaskExecution(): void
    {
        // arrange
        $order = file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json');
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');
        StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], '')
        ]);

        $signals = iterator_to_array($this->task->execute(), false);

        self::assertSame([100], $signals);
        self::assertTrue($this->task->getTransactionData()->isSuccessful());
    }

    public function testToArrayKeys(): void
    {
        self::assertSame(['orderUpdateData', 'transactionData', 'transactionLogId'], array_keys($this->task->toArray()));
        self::assertNull($this->task->toArray()['transactionLogId']);
    }

    public function testArrayRoundTripKeepsTheTransactionLogId(): void
    {
        $this->task->setTransactionLogId(42);

        $copy = TransactionalOrderUpdateTask::fromArray($this->task->toArray());

        self::assertSame($this->task->toArray(), $copy->toArray());
        self::assertSame(42, $copy->getTransactionLogId());
    }

    public function testIsABusinessTask(): void
    {
        self::assertInstanceOf(BusinessTaskInterface::class, $this->task);
    }
}
