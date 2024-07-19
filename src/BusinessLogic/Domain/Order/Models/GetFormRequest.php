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
     * @param string $orderId
     * @param string|null $product
     * @param string|null $campaign
     * @param bool|null $ajax
     */
    public function __construct(string $orderId, string $product = null, string $campaign = null, bool $ajax = null)
    {
        $this->orderId = $orderId;
        $this->product = $product;
        $this->campaign = $campaign;
        $this->ajax = $ajax;
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
     * Create a GetFormRequest instance from an array.
     *
     * @param array $data
     *
     * @return GetFormRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'order_id'),
            self::getDataValue($data, 'product', null),
            self::getDataValue($data, 'campaign', null),
            self::getDataValue($data, 'ajax', null)
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

        return $data;
    }
}
