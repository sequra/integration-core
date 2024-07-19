<?php

namespace SeQura\Core\BusinessLogic\Webhook\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class OrderUpdateTask
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Tasks
 */
class OrderUpdateTask extends Task
{
    /**
     * @var Webhook
     */
    protected $webhook;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * Transforms array into an order update task object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return OrderUpdateTask  Instance of an order update task object.
     *
     * @throws Exception
     */
    public static function fromArray(array $array): OrderUpdateTask
    {
        return StoreContext::doWithStore($array['storeId'], static function () use ($array) {
            return new static(
                Webhook::fromArray($array['webhook'])
            );
        });
    }

    /**
     * @param Webhook $webhook
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * Runs task logic.
     *
     * @return void
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore(
            $this->storeId,
            function () {
                $this->doExecute();
            }
        );
    }

    /**
     * Executes task behavior.
     *
     * @return void
     */
    protected function doExecute(): void
    {
        $shopStatus = $this->getOrderStatusMappingService()->getMapping($this->webhook->getSqState());
        $this->getShopOrderService()->updateStatus($this->webhook, $shopStatus);

        $this->reportProgress(100);
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray(): array
    {
        return [
            'webhook' => $this->webhook->toArray(),
            'storeId' => $this->storeId,
        ];
    }

    /**
     * @inheritDocs
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDocs
     */
    public function __unserialize($data): void
    {
        $this->webhook = Webhook::fromArray($data['webhook']);
        $this->storeId = $data['storeId'];
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return Serializer::serialize(array($this->webhook, $this->storeId));
    }

    /**
     * Constructs the object
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        [$this->webhook, $this->storeId] = Serializer::unserialize($serialized);
    }

    /**
     * Returns an instance of the status mapping service.
     *
     * @return OrderStatusSettingsService
     */
    protected function getOrderStatusMappingService(): OrderStatusSettingsService
    {
        return ServiceRegister::getService(OrderStatusSettingsService::class);
    }

    /**
     * Returns an instance of the shop order service.
     *
     * @return ShopOrderService
     */
    protected function getShopOrderService(): ShopOrderService
    {
        return ServiceRegister::getService(ShopOrderService::class);
    }
}
