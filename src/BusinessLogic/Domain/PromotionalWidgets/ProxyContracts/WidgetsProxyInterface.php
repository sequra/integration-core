<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;

/**
 * Interface WidgetsProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts
 */
interface WidgetsProxyInterface
{
    /**
     * Validates assets key.
     *
     * @param ValidateAssetsKeyRequest $request
     *
     * @return void
     *
     * @throws Exception
     */
    public function validateAssetsKey(ValidateAssetsKeyRequest $request): void;
}
