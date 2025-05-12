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
     * @var array<string>
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

    /**
     * @param string $assetKey
     * @param string $merchantId
     * @param array<string> $products
     * @param string $scriptUri
     * @param string $locale
     * @param string $currency
     * @param string $decimalSeparator
     * @param string $thousandSeparator
     */
    public function __construct(
        string $assetKey,
        string $merchantId,
        array $products,
        string $scriptUri,
        string $locale = 'es-ES',
        string $currency = 'eur',
        string $decimalSeparator = ',',
        string $thousandSeparator = '.'
    ) {
        $this->assetKey = $assetKey;
        $this->merchantId = $merchantId;
        $this->products = $products;
        $this->scriptUri = $scriptUri;
        $this->locale = $locale;
        $this->currency = $currency;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandSeparator = $thousandSeparator;
    }

    /**
     * @return string
     */
    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return string
     */
    public function getScriptUri(): string
    {
        return $this->scriptUri;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    /**
     * @return string
     */
    public function getThousandSeparator(): string
    {
        return $this->thousandSeparator;
    }
}
