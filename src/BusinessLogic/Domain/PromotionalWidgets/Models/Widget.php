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
 * @var string $minAmount
*/
    protected $minAmount;
    /**
 * @var string $maxAmount
*/
    protected $maxAmount;
    /**
 * @var string $altPriceSel
*/
    protected $altPriceSel;
    /**
 * @var bool $isAltSel
*/
    protected $isAltSel;

    /**
     * @param string $product
     * @param string $campaign
     * @param string $priceSel
     * @param string $dest
     * @param string $theme
     * @param string $reverse
     * @param string $minAmount
     * @param string $maxAmount
     * @param string $altPriceSel
     * @param bool $isAltSel
     */
    public function __construct(
        string $product,
        string $campaign,
        string $priceSel,
        string $dest,
        string $theme,
        string $reverse,
        string $minAmount = '',
        string $maxAmount = '',
        string $altPriceSel = '',
        bool $isAltSel = false
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
        $this->isAltSel = $isAltSel;
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
     * @return string
     */
    public function getMinAmount(): string
    {
        return $this->minAmount;
    }

    /**
     * @param string $minAmount
     */
    public function setMinAmount(string $minAmount): void
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return string
     */
    public function getMaxAmount(): string
    {
        return $this->maxAmount;
    }

    /**
     * @param string $maxAmount
     */
    public function setMaxAmount(string $maxAmount): void
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
     * @return bool
     */
    public function isAltSel(): bool
    {
        return $this->isAltSel;
    }

    /**
     * @param bool $isAltSel
     */
    public function setIsAltSel(bool $isAltSel): void
    {
        $this->isAltSel = $isAltSel;
    }
}
