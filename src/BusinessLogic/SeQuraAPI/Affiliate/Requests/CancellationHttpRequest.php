<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class CancellationHttpRequest.
 *
 * POST cancellation postback shaped for seQura's affiliate conversion-status webhook. The endpoint
 * is the destination path: the runtime router matches it and swaps the host to seQura's backend, preserving
 * path and body (host-swap contract, no rewrite). The status is always "cancelled".
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests
 */
class CancellationHttpRequest extends HttpRequest
{
    /**
     * Destination path expected by seQura's affiliate conversion-status webhook.
     */
    public const ENDPOINT = 'affiliate_network/webhooks/conversion_status';

    /**
     * Wire status is always "cancelled" regardless of the internal trigger.
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @param AffiliateCancellation $cancellation
     */
    public function __construct(AffiliateCancellation $cancellation)
    {
        parent::__construct(
            self::ENDPOINT,
            [
                'transaction_id' => $cancellation->getTransactionId(),
                'offer_id' => $cancellation->getOfferId(),
                'security_token' => $cancellation->getSecurityToken(),
                'status' => self::STATUS_CANCELLED,
            ]
        );
    }
}
