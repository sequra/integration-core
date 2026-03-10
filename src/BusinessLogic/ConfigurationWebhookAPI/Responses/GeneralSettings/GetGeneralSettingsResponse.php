<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\Product\Model\ShopProduct;

/**
 * Class GetGeneralSettingsResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings
 */
class GetGeneralSettingsResponse extends Response
{
    /**
     * @var GeneralSettings $generalSettings
     */
    protected $generalSettings;
    /**
     * @var ShopProduct[] $products
     */
    protected $products;

    /**
     * @var Category[] $categories
     */
    protected $categories;

    /**
     * @var string[] $sellingCountries
     */
    protected $sellingCountries;

    /**
     * @param GeneralSettings $generalSettings
     * @param ShopProduct[] $products
     * @param Category[] $categories
     * @param string[] $sellingCountries
     */
    public function __construct(GeneralSettings $generalSettings, array $products, array $categories, array $sellingCountries)
    {
        $this->generalSettings = $generalSettings;
        $this->products = $products;
        $this->categories = $categories;
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $response = $this->generalSettings->toArray();

        if (!empty($this->products)) {
            $response['excludedProducts'] = array_map(function (ShopProduct $product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getName()
                ];
            }, $this->products);
        }

        if (!empty($this->categories)) {
            $response['excludedCategories'] = array_map(function (Category $category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }, $this->categories);
        }

        $response['sellingCountries'] = $this->sellingCountries;

        return $response;
    }
}
