<?php

namespace SeQura\Core\BusinessLogic\WebhookAPI\Controller;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\BusinessLogic\WebhookAPI\Response\WebhookErrorResponse;
use SeQura\Core\BusinessLogic\WebhookAPI\Response\WebhookSuccessResponse;

/**
 * Class WebhookController
 *
 * @package SeQura\Core\BusinessLogic\WebhookAPI\Controller
 */
class WebhookController
{
    /**
     * @var WebhookValidator
     */
    protected $validator;

    /**
     * @var WebhookHandler
     */
    protected $handler;

    /**
     * WebhookController constructor.
     *
     * @param WebhookValidator $validator
     * @param WebhookHandler $handler
     */
    public function __construct(WebhookValidator $validator, WebhookHandler $handler)
    {
        $this->validator = $validator;
        $this->handler = $handler;
    }

    /**
     * Handles a webhook request from SeQura.
     *
     * SeQura expects an empty response. Expected response status values are 200, 201, 202, 302, 307, 404 and 501.
     * Other values will be considered errors. If the integration receives any unknown or unimplemented event type,
     * it should respond with a 501 Not Implemented response. If the integration encounters an error while updating
     * the target shop order status, it should respond with a 410 Gone response, to indicate to SeQura that the
     * target shop refuses to accept the order status update.
     *
     * @param array $payload
     *
     * @return Response
     */
    public function handleRequest(array $payload): Response
    {
        try {
            $webhook = Webhook::fromArray($payload);
            $this->validator->validate($webhook);
            $this->handler->handle($webhook);
        } catch (\Exception $e) {
            return new WebhookErrorResponse($e);
        }

        return new WebhookSuccessResponse();
    }
}
