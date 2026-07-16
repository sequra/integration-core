<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class ConversionHttpRequest.
 *
 * GET conversion postback shaped for the affiliate network. The endpoint is the
 * destination path: the runtime router matches it and swaps the host to the affiliate network,
 * preserving path and query string (host-swap contract, no rewrite).
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests
 */
class ConversionHttpRequest extends HttpRequest
{
    /**
     * Destination path expected by the affiliate network.
     */
    public const ENDPOINT = 'aff_lsr';

    /**
     * @param AffiliateConversion $conversion
     */
    public function __construct(AffiliateConversion $conversion)
    {
        parent::__construct(
            self::ENDPOINT,
            [],
            [
                'offer_id' => $conversion->getOfferId(),
                'amount' => number_format($conversion->getAmount(), 2, '.', ''),
                'transaction_id' => $conversion->getTransactionId(),
                'security_token' => $conversion->getSecurityToken(),
                'adv_sub' => $conversion->getOrderReference(),
            ]
        );
    }
}
