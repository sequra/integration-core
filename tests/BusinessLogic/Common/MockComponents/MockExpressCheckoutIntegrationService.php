<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutIntegrationInterface;

/**
 * Class MockExpressCheckoutIntegrationService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockExpressCheckoutIntegrationService implements ExpressCheckoutIntegrationInterface
{
    /**
     * @var ExpressCheckoutPage[]
     */
    private $availablePages;

    /**
     * @param ExpressCheckoutPage[]|null $availablePages Defaults to all known pages.
     */
    public function __construct(?array $availablePages = null)
    {
        $this->availablePages = $availablePages !== null ? $availablePages : [
            ExpressCheckoutPage::product(),
            ExpressCheckoutPage::cart(),
            ExpressCheckoutPage::miniCart(),
        ];
    }

    /**
     * @param ExpressCheckoutPage[] $availablePages
     *
     * @return void
     */
    public function setAvailablePages(array $availablePages): void
    {
        $this->availablePages = $availablePages;
    }

    /**
     * @inheritDoc
     */
    public function getAvailablePages(): array
    {
        return $this->availablePages;
    }
}
