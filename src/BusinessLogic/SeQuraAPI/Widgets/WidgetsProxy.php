<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Widgets;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpNoAvailablePaymentMethods;
use SeQura\Core\BusinessLogic\SeQuraAPI\Widgets\Requests\ValidateAssetsKeyHttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class WidgetsProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Widgets
 */
class WidgetsProxy extends BaseProxy implements WidgetsProxyInterface
{
    protected const BASE_API_URL = 'sequracdn.com';

    /**
     * Validates assets key.
     *
     * @param ValidateAssetsKeyRequest $request
     *
     * @return void
     *
     * @throws HttpNoAvailablePaymentMethods
     * @throws HttpRequestException
     */
    public function validateAssetsKey(ValidateAssetsKeyRequest $request): void
    {
        $this->mode = $request->getMode();
        $this->get(new ValidateAssetsKeyHttpRequest($request))->decodeBodyToArray();
    }
}
