<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class CartSummary
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var CartAddress
     */
    protected $shippingAddress;

    /**
     * @var ShippingMethodOption[]
     */
    protected $shippingMethods;

    /**
     * @var string|null
     */
    protected $appliedShippingMethodReference;

    /**
     * @var int|null
     */
    protected $shippingCostWithTax;

    /**
     * @var int
     */
    protected $totalWithTax;

    /**
     * @var RegionOption[]
     */
    protected $regionOptions;

    /**
     * @var string
     */
    protected $storeName;

    /**
     * @var string|null
     */
    protected $storeLogoUrl;

    /**
     * @var array<string, string>
     */
    protected $itemImages;

    /**
     * @param string $email
     * @param CartAddress $shippingAddress
     * @param ShippingMethodOption[] $shippingMethods
     * @param string|null $appliedShippingMethodReference
     * @param int|null $shippingCostWithTax
     * @param int $totalWithTax
     * @param RegionOption[] $regionOptions
     * @param string $storeName
     * @param string|null $storeLogoUrl
     * @param array<string, string> $itemImages
     */
    public function __construct(
        string $email,
        CartAddress $shippingAddress,
        array $shippingMethods,
        ?string $appliedShippingMethodReference,
        ?int $shippingCostWithTax,
        int $totalWithTax,
        array $regionOptions,
        string $storeName,
        ?string $storeLogoUrl,
        array $itemImages
    ) {
        $this->email = $email;
        $this->shippingAddress = $shippingAddress;
        $this->shippingMethods = $shippingMethods;
        $this->appliedShippingMethodReference = $appliedShippingMethodReference;
        $this->shippingCostWithTax = $shippingCostWithTax;
        $this->totalWithTax = $totalWithTax;
        $this->regionOptions = $regionOptions;
        $this->storeName = $storeName;
        $this->storeLogoUrl = $storeLogoUrl;
        $this->itemImages = $itemImages;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return CartAddress
     */
    public function getShippingAddress(): CartAddress
    {
        return $this->shippingAddress;
    }

    /**
     * @return ShippingMethodOption[]
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods;
    }

    /**
     * @return string|null
     */
    public function getAppliedShippingMethodReference(): ?string
    {
        return $this->appliedShippingMethodReference;
    }

    /**
     * @return int|null
     */
    public function getShippingCostWithTax(): ?int
    {
        return $this->shippingCostWithTax;
    }

    /**
     * @return int
     */
    public function getTotalWithTax(): int
    {
        return $this->totalWithTax;
    }

    /**
     * @return RegionOption[]
     */
    public function getRegionOptions(): array
    {
        return $this->regionOptions;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * @return string|null
     */
    public function getStoreLogoUrl(): ?string
    {
        return $this->storeLogoUrl;
    }

    /**
     * @return array<string, string>
     */
    public function getItemImages(): array
    {
        return $this->itemImages;
    }
}
