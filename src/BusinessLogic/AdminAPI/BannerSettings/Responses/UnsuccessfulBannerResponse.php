<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class UnsuccessfulBannerResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses
 */
class UnsuccessfulBannerResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = false;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var int
     */
    protected $statusCode = 400;

    /**
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message, int $statusCode = 400)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'errorCode' => $this->statusCode
        ];
    }
}
