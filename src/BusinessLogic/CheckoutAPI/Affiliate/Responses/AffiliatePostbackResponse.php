<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AffiliatePostbackResponse.
 *
 * Use isDispatched() / toArray()['sent'] to know whether a postback actually left the core.
 * isSuccessful() keeps its framework meaning ("the call completed without error") and stays true
 * on a disabled store, where nothing is dispatched: disabling affiliate marketing is a valid state,
 * not a failure. That distinction matters to the caller — a disabled store (successful, not sent)
 * must be told apart from a real dispatch error (not successful), which the facade surfaces as an
 * error response.
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
     * Whether a postback was actually dispatched (false when affiliate marketing is disabled).
     *
     * @return bool
     */
    public function isDispatched(): bool
    {
        return $this->sent;
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
