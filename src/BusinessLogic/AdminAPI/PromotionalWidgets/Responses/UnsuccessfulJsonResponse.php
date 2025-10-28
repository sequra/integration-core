<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class UnsuccessfulJsonResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses
 */
class UnsuccessfulJsonResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = false;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'message' => 'Invalid JSON.',
            'errorCode' => 400
        ];
    }
}
