<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;

/**
 * Class SolicitationRequest
 *
 * Carries the order builder plus the storefront context the eligibility guards need. Neither the
 * shipping country nor the shopper's IP is a field: both are read off the built order, so the guard
 * and the solicit itself can never disagree about the order they are deciding on.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests
 */
class SolicitationRequest
{
    /**
     * @var CreateOrderRequestBuilder
     */
    protected $builder;

    /**
     * @var string[]
     */
    protected $productIds;

    /**
     * @var string[]
     */
    protected $categoryIds;

    /**
     * @var bool
     */
    protected $checkCountry;

    /**
     * @param CreateOrderRequestBuilder $builder
     * @param string[] $productIds Product references in the cart (empty array = no per-product check).
     * @param string[] $categoryIds Category references in the cart (empty array = no per-category check).
     * @param bool $checkCountry When false, the shipping country guard is skipped.
     */
    public function __construct(
        CreateOrderRequestBuilder $builder,
        array $productIds = [],
        array $categoryIds = [],
        bool $checkCountry = true
    ) {
        $this->builder = $builder;
        $this->productIds = $productIds;
        $this->categoryIds = $categoryIds;
        $this->checkCountry = $checkCountry;
    }

    /**
     * @return CreateOrderRequestBuilder
     */
    public function getBuilder(): CreateOrderRequestBuilder
    {
        return $this->builder;
    }

    /**
     * @return string[]
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @return string[]
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    /**
     * @return bool
     */
    public function isCountryCheckEnabled(): bool
    {
        return $this->checkCountry;
    }
}
