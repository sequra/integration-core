<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;

/**
 * Class MockExpressCheckoutSettingsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockExpressCheckoutSettingsRepository implements ExpressCheckoutSettingsRepositoryInterface
{
    /**
     * @var ?ExpressCheckoutSettings
     */
    private $settings;

    /**
     * @inheritDoc
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function setExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function deleteExpressCheckoutSettings(): void
    {
        $this->settings = null;
    }
}
