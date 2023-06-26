<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\GeneralSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopCategoriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopProductsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\SuccessfulGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\Category\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\Product\Models\Product;

/**
 * Class GeneralSettingsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings
 */
class GeneralSettingsController
{
    /**
     * Gets active general settings.
     *
     * @return GeneralSettingsResponse
     */
    public function getGeneralSettings(): GeneralSettingsResponse
    {
        return new GeneralSettingsResponse(
            new GeneralSettings(
                true,
                false,
                null,
                null,
                null
            )
        );
    }

    /**
     * Saves new general settings.
     *
     * @param GeneralSettingsRequest $request
     *
     * @return SuccessfulGeneralSettingsResponse
     */
    public function saveGeneralSettings(GeneralSettingsRequest $request): SuccessfulGeneralSettingsResponse
    {
        return new SuccessfulGeneralSettingsResponse();
    }

    /**
     * Gets shop categories.
     *
     * @return ShopCategoriesResponse
     */
    public function getShopCategories(): ShopCategoriesResponse
    {
        return new ShopCategoriesResponse([
            new Category('cat1', 'Shoes'),
            new Category('cat2', 'Bags'),
            new Category('cat3', 'Watches'),
            new Category('cat4', 'Shirts'),
            new Category('cat5', 'Dresses')
        ]);
    }

    /**
     * Gets shop products.
     *
     * @return ShopProductsResponse
     */
    public function getShopProducts(): ShopProductsResponse
    {
        return new ShopProductsResponse([
            new Product('prod1', 'IPhone 5'),
            new Product('prod2', 'Red dress'),
            new Product('prod3', 'Black T-Shirt'),
            new Product('prod4', 'Xiaomi headphones'),
            new Product('prod5', 'Asus motherboard'),
            new Product('prod6', 'Nvidia GPU'),
            new Product('prod7', 'Logitech keyboard')
        ]);
    }
}
