<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Class GeneralSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses
 */
class GeneralSettingsResponse extends Response
{
    /**
     * @var GeneralSettings
     */
    protected $generalSettings;

    /**
     * @param GeneralSettings|null $generalSettings
     */
    public function __construct(?GeneralSettings $generalSettings)
    {
        $this->generalSettings = $generalSettings;
    }
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->generalSettings) {
            return [];
        }

        return[
            'sendOrderReportsPeriodicallyToSeQura' => $this->generalSettings->isSendOrderReportsPeriodicallyToSeQura(),
            'showSeQuraCheckoutAsHostedPage' => $this->generalSettings->isShowSeQuraCheckoutAsHostedPage(),
            'allowedIPAddresses' => $this->generalSettings->getAllowedIPAddresses(),
            'excludedProducts' => $this->generalSettings->getExcludedProducts(),
            'excludedCategories' => $this->generalSettings->getExcludedCategories(),
        ];
    }
}
