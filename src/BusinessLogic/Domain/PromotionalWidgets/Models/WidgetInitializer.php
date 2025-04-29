<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetInitializer
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetInitializer
{
    /**
     * @var string
     */
    protected $assetKey;
    /**
     * @var string
     */
    protected $merchantId;
    /**
     * @var array
     */
    protected $products;
    /**
     * @var string
     */
    protected $scriptUri;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $decimalSeparator;
    /**
     * @var string
     */
    protected $thousandSeparator;

    public function __construct(
        string $assetKey,
        string $merchantId,
        array $products,
        string $scriptUri,
        string $locale = 'en',
        string $currency = 'eur',
        string $decimalSeparator = ',',
        string $thousandSeparator = '.'
    )
    {
        $this->assetKey = $assetKey;
        $this->merchantId = $merchantId;
        $this->products = $products;
        $this->scriptUri = $scriptUri;
        $this->locale = $locale;
        $this->currency = $currency;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandSeparator = $thousandSeparator;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getScriptUri(): string
    {
        return $this->scriptUri;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function getThousandSeparator(): string
    {
        return $this->thousandSeparator;
    }
}