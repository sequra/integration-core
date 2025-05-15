<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;

/**
 * Class MockWidgetProxy.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetProxy implements WidgetsProxyInterface
{
    /**
     * @inheritDoc
     */
    public function validateAssetsKey(ValidateAssetsKeyRequest $request): void
    {
    }
}
