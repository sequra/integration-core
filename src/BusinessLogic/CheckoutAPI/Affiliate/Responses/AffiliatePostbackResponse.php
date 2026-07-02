<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AffiliatePostbackResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Responses
 */
class AffiliatePostbackResponse extends Response
{
    /**
     * Whether a postback was actually sent and accepted (false when affiliate marketing is
     * disabled for the store, so nothing was dispatched).
     *
     * @var bool
     */
    protected $sent;

    /**
     * @param bool $sent
     */
    public function __construct(bool $sent)
    {
        $this->sent = $sent;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'sent' => $this->sent,
        ];
    }
}
