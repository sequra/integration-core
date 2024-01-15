<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Tasks;

use Exception;
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
use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionalOrderUpdateTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Tasks
 */
class TransactionalOrderUpdateTaskTest extends BaseSerializationTestCase
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

        $this->serializable = new TransactionalOrderUpdateTask($orderUpdateData, $transactionData);
    }

    /**
     * @doesNotPerformAssertions
     *
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

        $this->serializable->execute();
    }
}
