<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiRequestException;

/**
 * Class OrderUpdateRejectedException
 *
 * SeQura refused to finance the order as an update raised it, with the reason it gave.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class OrderUpdateRejectedException extends BaseTranslatableException
{
    /**
     * Error code the API answers a refused update with.
     */
    public const ERROR_CODE = 'general.errors.order.updateRejected';

    /**
     * What SeQura's reason says when it refuses such an update. It answers an unknown merchant with the same status,
     * so the reason is what tells the two apart.
     */
    public const UPSELL_REFUSAL = 'cannot upsell';

    /**
     * @var int
     */
    protected $code = 403;

    /**
     * @param HttpApiRequestException $previous SeQura's refusal
     */
    public function __construct(HttpApiRequestException $previous)
    {
        parent::__construct(new TranslatableLabel(
            $previous->getMessage(),
            self::ERROR_CODE
        ), $previous);
    }
}
