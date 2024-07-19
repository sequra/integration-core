<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Exceptions\InvalidOrderDeliveryStateException;

/**
 * Class OrderDeliveryReportRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderRequest
 */
class SendOrderReportRequest extends OrderRequestDTO
{
    /**
     * @var Merchant Fields describing the merchant.
     */
    protected $merchant;

    /**
     * @var OrderReport[] A list of order reports.
     */
    protected $orders;

    /**
     * @var Statistics|null Filed containing a list of statistical order data.
     */
    protected $statistics;

    /**
     * @var Platform Fields describing the store platform.
     */
    protected $platform;

    /**
     * @param Merchant $merchant
     * @param OrderReport[] $orders
     * @param Statistics|null $statistics
     * @param Platform $platform
     *
     * @throws InvalidUrlException
     */
    public function __construct(Merchant $merchant, array $orders, Platform $platform, ?Statistics $statistics = null)
    {
        $merchantId = $merchant->getId();

        $this->merchant = new Merchant($merchantId);
        $this->orders = $orders;
        $this->statistics = $statistics;
        $this->platform = $platform;
    }

    /**
     * Creates a new OrderDeliveryReportRequest instance from an array.
     *
     * @param array $data
     *
     * @return self
     *
     * @throws InvalidTimestampException
     * @throws InvalidUrlException
     * @throws InvalidCartItemsException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidOrderDeliveryStateException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     */
    public static function fromArray(array $data): self
    {
        $merchantData = Merchant::fromArray(self::getDataValue($data, 'merchant', []));

        $merchant = Merchant::fromArray(['id' => $merchantData->getId()]);
        $platform = Platform::fromArray(self::getDataValue($data, 'platform', []));
        $orders = array_map(static function ($orderReport) {
            return OrderReport::fromArray($orderReport);
        }, self::getDataValue($data, 'orders', []));

        $statistics = self::getDataValue($data, 'statistics', null);
        if ($statistics !== null) {
            $statistics = Statistics::fromArray($statistics);
        }

        return new self(
            $merchant,
            $orders,
            $platform,
            $statistics
        );
    }

    /**
     * @return Merchant
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * @return OrderReport[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @return Statistics|null
     */
    public function getStatistics(): ?Statistics
    {
        return $this->statistics;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
