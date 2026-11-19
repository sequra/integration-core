<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class UpdateMerchantReferenceHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class UpdateMerchantReferenceHttpRequest extends HttpRequest
{
    /**
     * @param string $merchantId
     * @param string $shopOrderReference The reference seQura knows the order by now.
     * @param MerchantReference $merchantReference The references the order is known by from now on.
     */
    public function __construct(
        string $merchantId,
        string $shopOrderReference,
        MerchantReference $merchantReference
    ) {
        parent::__construct(
            'merchants/' . $merchantId . '/orders/' . rawurlencode($shopOrderReference) . '/merchant_reference',
            ['merchant_reference' => $merchantReference->toArray()]
        );
    }
}
