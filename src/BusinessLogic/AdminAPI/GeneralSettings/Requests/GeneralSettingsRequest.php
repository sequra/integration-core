<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Category\Exceptions\EmptyCategoryParameterException;
use SeQura\Core\BusinessLogic\Domain\Category\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\Product\Exceptions\EmptyProductParameterException;
use SeQura\Core\BusinessLogic\Domain\Product\Models\Product;

/**
 * Class GeneralSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
class GeneralSettingsRequest extends Request
{
    /**
     * @var bool
     */
    private $showSeQuraCheckoutAsHostedPage;

    /**
     * @var bool
     */
    private $sendOrderReportsPeriodicallyToSeQura;

    /**
     * @var string[]|null
     */
    private $allowedIPAddresses;

    /**
     * @var array|null
     */
    private $excludedCategories;

    /**
     * @var array|null
     */
    private $excludedProducts;

    /**
     * @param bool $showSeQuraCheckoutAsHostedPage
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param string[]|null $allowedIPAddresses
     * @param array|null $excludedCategories
     * @param array|null $excludedProducts
     */
    public function __construct(
        bool $showSeQuraCheckoutAsHostedPage,
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?array $allowedIPAddresses,
        ?array $excludedCategories,
        ?array $excludedProducts
    )
    {
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedCategories = $excludedCategories;
        $this->excludedProducts = $excludedProducts;
    }

    /**
     * Transforms the request to a GeneralSettings object.
     *
     * @return GeneralSettings
     *
     * @throws EmptyProductParameterException
     * @throws EmptyCategoryParameterException
     */
    public function transformToDomainModel(): object
    {
        $categories = [];
        foreach ($this->excludedCategories as $category) {
            $categories = new Category(
                $category['categoryId'] ?? '',
                $category['categoryName'] ?? ''
            );
        }

        $products = [];
        foreach ($this->excludedProducts as $product) {
            $products = new Product(
                $product['productId'] ?? '',
                $product['productName'] ?? ''
            );
        }

        return new GeneralSettings(
            $this->showSeQuraCheckoutAsHostedPage,
            $this->sendOrderReportsPeriodicallyToSeQura,
            $this->allowedIPAddresses,
            $categories,
            $products
        );
    }
}
