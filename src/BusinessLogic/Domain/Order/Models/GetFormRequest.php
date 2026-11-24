<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetFormRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class GetFormRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string|null
     */
    protected $product;

    /**
     * @var string|null
     */
    protected $campaign;

    /**
     * @var bool|null
     */
    protected $ajax;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var bool|null
     */
    protected $showCart;

    /**
     * @param string $orderId
     * @param string|null $product
     * @param string|null $campaign
     * @param bool|null $ajax
     * @param string $merchantId
     * @param bool|null $showCart
     */
    public function __construct(
        string $orderId,
        ?string $product = null,
        ?string $campaign = null,
        ?bool $ajax = null,
        string $merchantId = '',
        ?bool $showCart = null
    ) {
        $this->orderId = $orderId;
        $this->product = $product;
        $this->campaign = $campaign;
        $this->ajax = $ajax;
        $this->merchantId = $merchantId;
        $this->showCart = $showCart;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getProduct(): ?string
    {
        return $this->product;
    }

    /**
     * @return string|null
     */
    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    /**
     * @return bool|null
     */
    public function getAjax(): ?bool
    {
        return $this->ajax;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getShowCart(): ?bool
    {
        return $this->showCart;
    }

    /**
     * Create a GetFormRequest instance from an array.
     *
     * @param mixed[] $data
     *
     * @return GetFormRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'order_id'),
            self::getDataValue($data, 'product', null),
            self::getDataValue($data, 'campaign', null),
            self::getDataValue($data, 'ajax', null),
            self::getDataValue($data, 'merchantId', ''),
            self::getDataValue($data, 'show_cart', null)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['order_id'] = $this->orderId;
        $data['product'] = $this->product;
        $data['campaign'] = $this->campaign;
        $data['ajax'] = $this->ajax;
        $data['merchantId'] = $this->merchantId;
        $data['show_cart'] = $this->showCart;

        return $data;
    }
}
