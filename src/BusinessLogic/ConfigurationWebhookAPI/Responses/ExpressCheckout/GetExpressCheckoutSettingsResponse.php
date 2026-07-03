<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;

/**
 * Class GetExpressCheckoutSettingsResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout
 */
class GetExpressCheckoutSettingsResponse extends Response
{
    /**
     * @var ExpressCheckoutPage[]
     */
    protected $availablePages;

    /**
     * @var ExpressCheckoutSettings|null
     */
    protected $expressCheckoutSettings;

    /**
     * @param ExpressCheckoutPage[] $availablePages
     * @param ExpressCheckoutSettings|null $expressCheckoutSettings
     */
    public function __construct(array $availablePages, ?ExpressCheckoutSettings $expressCheckoutSettings)
    {
        $this->availablePages = $availablePages;
        $this->expressCheckoutSettings = $expressCheckoutSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $configs = $this->expressCheckoutSettings
            ? $this->expressCheckoutSettings->getExpressCheckoutConfigs()
            : [];

        return [
            'availablePages' => array_map(static function (ExpressCheckoutPage $page) {
                return $page->getPage();
            }, $this->availablePages),
            'expressCheckoutConfigs' => array_map(static function (ExpressCheckoutPageConfig $config) {
                return $config->toArray();
            }, $configs),
        ];
    }
}
