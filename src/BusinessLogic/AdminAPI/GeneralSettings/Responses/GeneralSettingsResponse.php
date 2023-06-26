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
    private $generalSettings;

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
        $categoriesResponse = new ShopCategoriesResponse($this->generalSettings->getExcludedCategories());
        $productsResponse = new ShopProductsResponse($this->generalSettings->getExcludedProducts());

        return[
            'showSeQuraCheckoutAsHostedPage' => $this->generalSettings->isShowSeQuraCheckoutAsHostedPage(),
            'sendOrderReportsPeriodicallyToSeQura' => $this->generalSettings->isSendOrderReportsPeriodicallyToSeQura(),
            'allowedIPAddresses' => $this->generalSettings->getAllowedIPAddresses(),
            'excludedCategories' => $categoriesResponse->toArray(),
            'excludedProducts' => $productsResponse->toArray()
        ];
    }
}
