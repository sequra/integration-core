<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout\SaveExpressCheckoutSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout\SaveExpressCheckoutSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutSettingsService;

/**
 * Class SaveExpressCheckoutSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout
 */
class SaveExpressCheckoutSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var ExpressCheckoutSettingsService
     */
    protected $expressCheckoutSettingsService;

    /**
     * @param ExpressCheckoutSettingsService $expressCheckoutSettingsService
     */
    public function __construct(ExpressCheckoutSettingsService $expressCheckoutSettingsService)
    {
        $this->expressCheckoutSettingsService = $expressCheckoutSettingsService;
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
        $this->expressCheckoutSettingsService->saveExpressCheckoutSettings($request->transformToDomainModel());

        return new SaveExpressCheckoutSettingsResponse();
    }
}
