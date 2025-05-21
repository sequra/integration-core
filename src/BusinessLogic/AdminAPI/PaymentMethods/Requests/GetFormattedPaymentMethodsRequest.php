<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetFormattedPaymentMethodsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests
 */
class GetFormattedPaymentMethodsRequest extends DataTransferObject
{
    /**
     * @var bool
     */
    protected $cache;

    /**
     * @param bool $cache
     */
    public function __construct(bool $cache = false)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['cache'] = $this->cache;

        return $data;
    }

    /**
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }
}
