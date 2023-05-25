<?php

namespace SeQura\Core\BusinessLogic\Webhook\Handler;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidOrderStateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\OrderRequestStatusMapping;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;

/**
 * Class WebhookHandler
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Handler
 */
class WebhookHandler
{
    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * @var QueueNameProviderInterface
     */
    private $queueNameProvider;

    /**
     * WebhookHandler constructor.
     *
     * @param QueueService $queueService
     * @param QueueNameProviderInterface $queueNameProvider
     */
    public function __construct(QueueService $queueService, QueueNameProviderInterface $queueNameProvider)
    {
        $this->queueService = $queueService;
        $this->queueNameProvider = $queueNameProvider;
    }

    /**
     * Handles an incoming webhook request.
     *
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidCartItemsException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     * @throws InvalidTimestampException
     * @throws InvalidUrlException
     * @throws QueryFilterInvalidParamException
     * @throws QueueStorageUnavailableException
     * @throws RepositoryNotRegisteredException
     * @throws InvalidOrderStateException
     */
    public function handle(Webhook $webhook): void
    {
        $configurationManager = ServiceRegister::getService(ConfigurationManager::class);
        $task  = new OrderUpdateTask($webhook);

        $this->queueService->enqueue(
            $this->queueNameProvider->getQueueName($task),
            $task,
            $configurationManager->getContext()
        );

        if (in_array($webhook->getSqState(), [OrderStates::STATE_APPROVED, OrderStates::STATE_NEEDS_REVIEW], true)) {
            $this->acknowledgeOrder($webhook->getOrderRef(), $webhook->getSqState());
        }
    }


    /**
     * Acknowledges the order and its state to SeQura API.
     *
     * @param string $orderReference
     * @param string $state
     *
     * @return void
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws InvalidOrderStateException
     */
    private function acknowledgeOrder(string $orderReference, string $state): void
    {
        /** @var OrderProxy $orderProxy */
        $orderProxy = ServiceRegister::getService(OrderProxyInterface::class);
        $order = $this->getSeQuraOrderByOrderReference($orderReference);

        $request = new CreateOrderRequest(
            OrderRequestStatusMapping::mapOrderRequestStatus($state),
            $order->getMerchant(),
            $order->getCart(),
            $order->getDeliveryMethod(),
            $order->getCustomer(),
            $order->getPlatform(),
            $order->getDeliveryAddress(),
            $order->getInvoiceAddress(),
            $order->getGui(),
            $order->getMerchantReference()
        );

        $orderProxy->updateOrder($orderReference, $request);
    }

    /**
     * Retrieves the SeQuraOrder by orderRef1
     *
     * @param string $orderRef
     *
     * @return SeQuraOrder|null
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getSeQuraOrderByOrderReference(string $orderRef): ?SeQuraOrder
    {
        $repository = RepositoryRegistry::getRepository(SeQuraOrder::getClassName());

        $filter = new QueryFilter();
        $filter->where('reference', Operators::EQUALS, $orderRef);

        /** @var SeQuraOrder $order */
        $order = $repository->selectOne($filter);

        return $order;
    }
}
