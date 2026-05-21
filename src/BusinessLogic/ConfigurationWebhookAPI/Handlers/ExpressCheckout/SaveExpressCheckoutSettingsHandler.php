<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout\SaveExpressCheckoutSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout\SaveExpressCheckoutSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;

/**
 * Class SaveExpressCheckoutSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout
 */
class SaveExpressCheckoutSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var ExpressCheckoutService
     */
    protected $expressCheckoutService;

    /**
     * @param ExpressCheckoutService $expressCheckoutService
     */
    public function __construct(ExpressCheckoutService $expressCheckoutService)
    {
        $this->expressCheckoutService = $expressCheckoutService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function handle(array $payload): Response
    {
        $request = SaveExpressCheckoutSettingsRequest::fromPayload($payload);
        $this->expressCheckoutService->saveExpressCheckoutSettings($request->transformToDomainModel());

        return new SaveExpressCheckoutSettingsResponse();
    }
}
