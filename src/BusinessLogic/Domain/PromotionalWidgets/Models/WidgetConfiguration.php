<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetConfiguration
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetConfiguration
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $size;
    /**
     * @var string
     */
    private $fontColor;
    /**
     * @var string
     */
    private $backgroundColor;
    /**
     * @var string
     */
    private $alignment;
    /**
     * @var string
     */
    private $branding;
    /**
     * @var string
     */
    private $startingText;
    /**
     * @var string
     */
    private $amountFontSize;
    /**
     * @var string
     */
    private $amountFontColor;
    /**
     * @var string
     */
    private $amountFontBold;
    /**
     * @var string
     */
    private $linkFontColor;
    /**
     * @var string
     */
    private $linkUnderline;
    /**
     * @var string
     */
    private $borderColor;
    /**
     * @var string
     */
    private $borderRadius;
    /**
     * @var string
     */
    private $noCostsClaim;

    /**
     * @param string $type
     * @param string $size
     * @param string $fontColor
     * @param string $backgroundColor
     * @param string $alignment
     * @param string $branding
     * @param string $startingText
     * @param string $amountFontSize
     * @param string $amountColorSize
     * @param string $amountFontBold
     * @param string $linkFontColor
     * @param string $linkUnderline
     * @param string $borderColor
     * @param string $borderRadius
     * @param string $noCostsClaim
     */
    public function __construct(
        string $type = '',
        string $size = '',
        string $fontColor = '',
        string $backgroundColor = '',
        string $alignment = '',
        string $branding = '',
        string $startingText = '',
        string $amountFontSize = '',
        string $amountColorSize = '',
        string $amountFontBold = '',
        string $linkFontColor = '',
        string $linkUnderline = '',
        string $borderColor = '',
        string $borderRadius = '',
        string $noCostsClaim = ''
    )
    {
        $this->type = $type;
        $this->size = $size;
        $this->fontColor = $fontColor;
        $this->backgroundColor = $backgroundColor;
        $this->alignment = $alignment;
        $this->branding = $branding;
        $this->startingText = $startingText;
        $this->amountFontSize = $amountFontSize;
        $this->amountFontColor = $amountColorSize;
        $this->amountFontBold = $amountFontBold;
        $this->linkFontColor = $linkFontColor;
        $this->linkUnderline = $linkUnderline;
        $this->borderColor = $borderColor;
        $this->borderRadius = $borderRadius;
        $this->noCostsClaim = $noCostsClaim;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getFontColor(): string
    {
        return $this->fontColor;
    }

    /**
     * @param string $fontColor
     */
    public function setFontColor(string $fontColor): void
    {
        $this->fontColor = $fontColor;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     */
    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @return string
     */
    public function getAlignment(): string
    {
        return $this->alignment;
    }

    /**
     * @param string $alignment
     */
    public function setAlignment(string $alignment): void
    {
        $this->alignment = $alignment;
    }

    /**
     * @return string
     */
    public function getBranding(): string
    {
        return $this->branding;
    }

    /**
     * @param string $branding
     */
    public function setBranding(string $branding): void
    {
        $this->branding = $branding;
    }

    /**
     * @return string
     */
    public function getStartingText(): string
    {
        return $this->startingText;
    }

    /**
     * @param string $startingText
     */
    public function setStartingText(string $startingText): void
    {
        $this->startingText = $startingText;
    }

    /**
     * @return string
     */
    public function getAmountFontSize(): string
    {
        return $this->amountFontSize;
    }

    /**
     * @param string $amountFontSize
     */
    public function setAmountFontSize(string $amountFontSize): void
    {
        $this->amountFontSize = $amountFontSize;
    }

    /**
     * @return string
     */
    public function getAmountFontColor(): string
    {
        return $this->amountFontColor;
    }

    /**
     * @param string $amountFontColor
     */
    public function setAmountFontColor(string $amountFontColor): void
    {
        $this->amountFontColor = $amountFontColor;
    }

    /**
     * @return string
     */
    public function getAmountFontBold(): string
    {
        return $this->amountFontBold;
    }

    /**
     * @param string $amountFontBold
     */
    public function setAmountFontBold(string $amountFontBold): void
    {
        $this->amountFontBold = $amountFontBold;
    }

    /**
     * @return string
     */
    public function getLinkFontColor(): string
    {
        return $this->linkFontColor;
    }

    /**
     * @param string $linkFontColor
     */
    public function setLinkFontColor(string $linkFontColor): void
    {
        $this->linkFontColor = $linkFontColor;
    }

    /**
     * @return string
     */
    public function getLinkUnderline(): string
    {
        return $this->linkUnderline;
    }

    /**
     * @param string $linkUnderline
     */
    public function setLinkUnderline(string $linkUnderline): void
    {
        $this->linkUnderline = $linkUnderline;
    }

    /**
     * @return string
     */
    public function getBorderColor(): string
    {
        return $this->borderColor;
    }

    /**
     * @param string $borderColor
     */
    public function setBorderColor(string $borderColor): void
    {
        $this->borderColor = $borderColor;
    }

    /**
     * @return string
     */
    public function getBorderRadius(): string
    {
        return $this->borderRadius;
    }

    /**
     * @param string $borderRadius
     */
    public function setBorderRadius(string $borderRadius): void
    {
        $this->borderRadius = $borderRadius;
    }

    /**
     * @return string
     */
    public function getNoCostsClaim(): string
    {
        return $this->noCostsClaim;
    }

    /**
     * @param string $noCostsClaim
     */
    public function setNoCostsClaim(string $noCostsClaim): void
    {
        $this->noCostsClaim = $noCostsClaim;
    }
}