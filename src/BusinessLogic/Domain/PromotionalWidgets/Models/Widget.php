<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class Widget
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class Widget
{
    /**
 * @var string $product
*/
    protected $product;
    /**
 * @var string $campaign
*/
    protected $campaign;
    /**
 * @var string $priceSel
*/
    protected $priceSel;
    /**
 * @var string $dest
*/
    protected $dest;
    /**
 * @var string $theme
*/
    protected $theme;
    /**
 * @var string $reverse
*/
    protected $reverse;
    /**
 * @var int $minAmount
*/
    protected $minAmount;
    /**
 * @var int $maxAmount
*/
    protected $maxAmount;
    /**
 * @var string $altPriceSel
*/
    protected $altPriceSel;
    /**
 * @var string $altTriggerSelector
*/
    protected $altTriggerSelector;

    /**
     * @param string $product
     * @param string $campaign
     * @param string $priceSel
     * @param string $dest
     * @param string $theme
     * @param string $reverse
     * @param int $minAmount
     * @param int $maxAmount
     * @param string $altPriceSel
     * @param string $altTriggerSelector
     */
    public function __construct(
        string $product,
        string $campaign,
        string $priceSel,
        string $dest,
        string $theme,
        string $reverse,
        int $minAmount = 0,
        int $maxAmount = 0,
        string $altPriceSel = '',
        string $altTriggerSelector = ''
    ) {
        $this->product = $product;
        $this->campaign = $campaign;
        $this->priceSel = $priceSel;
        $this->dest = $dest;
        $this->theme = $theme;
        $this->reverse = $reverse;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
        $this->altPriceSel = $altPriceSel;
        $this->altTriggerSelector = $altTriggerSelector;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getCampaign(): string
    {
        return $this->campaign;
    }

    /**
     * @param string $campaign
     */
    public function setCampaign(string $campaign): void
    {
        $this->campaign = $campaign;
    }

    /**
     * @return string
     */
    public function getAltPriceSel(): string
    {
        return $this->altPriceSel;
    }

    /**
     * @param string $altPriceSel
     */
    public function setAltPriceSel(string $altPriceSel): void
    {
        $this->altPriceSel = $altPriceSel;
    }

    /**
     * @return string
     */
    public function getDest(): string
    {
        return $this->dest;
    }

    /**
     * @param string $dest
     */
    public function setDest(string $dest): void
    {
        $this->dest = $dest;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * @return int
     */
    public function getMinAmount(): int
    {
        return $this->minAmount;
    }

    /**
     * @param int $minAmount
     */
    public function setMinAmount(int $minAmount): void
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return int
     */
    public function getMaxAmount(): int
    {
        return $this->maxAmount;
    }

    /**
     * @param int $maxAmount
     */
    public function setMaxAmount(int $maxAmount): void
    {
        $this->maxAmount = $maxAmount;
    }

    /**
     * @return string
     */
    public function getPriceSel(): string
    {
        return $this->priceSel;
    }

    /**
     * @param string $priceSel
     */
    public function setPriceSel(string $priceSel): void
    {
        $this->priceSel = $priceSel;
    }

    /**
     * @param string $reverse
     */
    public function setReverse(string $reverse): void
    {
        $this->reverse = $reverse;
    }

    /**
     * @return string
     */
    public function getReverse(): string
    {
        return $this->reverse;
    }

    /**
     * @return string
     */
    public function getAltTriggerSelector(): string
    {
        return $this->altTriggerSelector;
    }

    /**
     * @param string $altTriggerSelector
     */
    public function setAltTriggerSelector(string $altTriggerSelector): void
    {
        $this->altTriggerSelector = $altTriggerSelector;
    }
}
