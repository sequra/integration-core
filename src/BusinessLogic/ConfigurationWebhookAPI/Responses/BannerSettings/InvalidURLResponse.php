<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class InvalidURLResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings
 */
class InvalidURLResponse extends Response
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
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'errorMessage' => $this->message,
            'errorCode' => 'INVALID_URL'
        ];
    }
}
