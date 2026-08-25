<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;

/**
 * Class SolicitationRequest
 *
 * Carries the order builder plus the storefront context the eligibility guards need. The shipping
 * country is not a field: it is read from the built order's delivery address, so the guard and the
 * solicit itself can never disagree about which country is being used.
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
     * @var string
     */
    protected $ipAddress;

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
     * @param string $ipAddress IP address of the storefront customer. Empty skips the IP guard.
     * @param string[] $productIds Product references in the cart (empty array = no per-product check).
     * @param string[] $categoryIds Category references in the cart (empty array = no per-category check).
     * @param bool $checkCountry When false, the shipping country guard is skipped.
     */
    public function __construct(
        CreateOrderRequestBuilder $builder,
        string $ipAddress = '',
        array $productIds = [],
        array $categoryIds = [],
        bool $checkCountry = true
    ) {
        $this->builder = $builder;
        $this->ipAddress = $ipAddress;
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
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
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
