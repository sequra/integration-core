<?php

namespace SeQura\Core\Tests\BusinessLogic\Webhook\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;

class WebhookHandlerTest extends BaseTestCase
{
    private const ORDER_REF = 'ord-1';

    /**
     * @var string[]
     */
    private $calls = [];

    /**
     * @var MockObject&OrderProxyInterface
     */
    private $orderProxy;

    /**
     * @var MockObject&ShopOrderService
     */
    private $shopOrderService;

    /**
     * @var MockObject&OrderStatusSettingsService
     */
    private $statusSettings;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->calls = [];
        $this->storeOrder();

        $this->orderProxy = $this->createMock(OrderProxyInterface::class);
        $this->orderProxy->method('acknowledgeOrder')->willReturnCallback(function () {
            $this->calls[] = 'acknowledge';

            return new SeQuraOrder();
        });
        $this->shopOrderService = $this->newShopOrderService();
        $this->statusSettings = $this->createMock(OrderStatusSettingsService::class);

        ServiceRegister::registerService(OrderProxyInterface::class, function () {
            return $this->orderProxy;
        });
        ServiceRegister::registerService(ShopOrderService::class, function () {
            return $this->shopOrderService;
        });
        ServiceRegister::registerService(OrderStatusSettingsService::class, function () {
            return $this->statusSettings;
        });
    }

    /**
     * @return void
     */
    public function testApprovedAcknowledgesUpdatesAndAcknowledgesAgain(): void
    {
        $this->statusSettings->method('getMapping')->with('approved')->willReturn('processing');
        $webhook = $this->webhook('approved');
        $this->shopOrderService->expects(self::once())->method('updateStatus')->with($webhook, 'processing');

        $this->handler()->handle($webhook);

        self::assertSame(['acknowledge', 'update', 'acknowledge'], $this->calls);
    }

    /**
     * @return void
     */
    public function testNeedsReviewAcknowledgesUpdatesAndAcknowledgesAgain(): void
    {
        $this->statusSettings->method('getMapping')->with('needs_review')->willReturn('on-hold');
        $webhook = $this->webhook('needs_review');
        $this->shopOrderService->expects(self::once())->method('updateStatus')->with($webhook, 'on-hold');

        $this->handler()->handle($webhook);

        self::assertSame(['acknowledge', 'update', 'acknowledge'], $this->calls);
    }

    /**
     * @return void
     */
    public function testCancelledUpdatesWithoutAcknowledging(): void
    {
        $this->statusSettings->method('getMapping')->with('cancelled')->willReturn('cancelled-in-shop');
        $webhook = $this->webhook('cancelled');
        $this->shopOrderService->expects(self::once())->method('updateStatus')->with($webhook, 'cancelled-in-shop');

        $this->handler()->handle($webhook);

        self::assertSame(['update'], $this->calls);
    }

    /**
     * @return void
     */
    public function testUnmappedStateUpdatesWithEmptyStatus(): void
    {
        $this->statusSettings->method('getMapping')->willReturn('');
        $webhook = $this->webhook('approved');
        $this->shopOrderService->expects(self::once())->method('updateStatus')->with($webhook, '');

        $this->handler()->handle($webhook);
    }

    /**
     * @return void
     */
    public function testShopUpdateFailureIsRethrownWithoutSecondAcknowledgement(): void
    {
        $exception = new RuntimeException('shop failure');
        $this->statusSettings->method('getMapping')->willReturn('processing');
        $this->shopOrderService->method('updateStatus')->willThrowException($exception);

        try {
            $this->handler()->handle($this->webhook('approved'));
            self::fail('Expected exception was not thrown.');
        } catch (RuntimeException $e) {
            self::assertSame($exception, $e);
        }

        self::assertSame(['acknowledge', 'update'], $this->calls);
    }

    /**
     * @return void
     */
    public function testUpdateRunsInRequestStore(): void
    {
        $this->statusSettings->method('getMapping')->willReturn('processing');
        $storeIds = [];
        $this->shopOrderService->method('updateStatus')->willReturnCallback(
            function () use (&$storeIds) {
                $storeIds[] = StoreContext::getInstance()->getStoreId();
            }
        );

        StoreContext::doWithStore('store-7', function () {
            $this->handler()->handle($this->webhook('approved'));
        });

        self::assertSame(['store-7'], $storeIds);
    }

    /**
     * @return void
     */
    public function testNoQueueItemIsCreated(): void
    {
        $this->statusSettings->method('getMapping')->willReturn('processing');
        $queueRepository = RepositoryRegistry::getQueueItemRepository();

        foreach (['approved', 'needs_review', 'cancelled'] as $state) {
            $this->handler()->handle($this->webhook($state));
        }

        self::assertCount(0, $queueRepository->select());
    }

    /**
     * @return void
     */
    public function testUpdateUsesShopOrderServiceFromRegister(): void
    {
        $this->statusSettings->method('getMapping')->willReturn('processing');
        $registered = $this->newShopOrderService();
        $registered->expects(self::once())->method('updateStatus');
        $this->shopOrderService->expects(self::never())->method('updateStatus');
        $handler = $this->handler();
        ServiceRegister::registerService(ShopOrderService::class, static function () use ($registered) {
            return $registered;
        });

        $handler->handle($this->webhook('approved'));
    }

    /**
     * @return WebhookHandler
     */
    private function handler(): WebhookHandler
    {
        return new WebhookHandler(
            $this->shopOrderService,
            $this->createMock(MerchantOrderRequestBuilder::class)
        );
    }

    /**
     * @return MockObject&ShopOrderService
     */
    private function newShopOrderService()
    {
        $service = $this->createMock(ShopOrderService::class);
        $service->method('getCreateOrderRequest')->willReturnCallback(function (string $reference) {
            return (new MockShopOrderService())->getCreateOrderRequest($reference);
        });
        $service->method('updateStatus')->willReturnCallback(function () {
            $this->calls[] = 'update';
        });

        return $service;
    }

    /**
     * @param string $state
     *
     * @return Webhook
     */
    private function webhook(string $state): Webhook
    {
        return Webhook::fromArray(['order_ref' => self::ORDER_REF, 'sq_state' => $state]);
    }

    /**
     * @return void
     */
    private function storeOrder(): void
    {
        $json = file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json');
        $order = SeQuraOrder::fromArray(json_decode($json, true)['order']);
        $order->setReference(self::ORDER_REF);
        RepositoryRegistry::getRepository(SeQuraOrder::getClassName())->save($order);
    }
}
