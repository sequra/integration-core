<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

/**
 * Class WidgetSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests
 */
class WidgetSettingsRequest extends Request
{
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var string|null
     */
    private $assetsKey;
    /**
     * @var bool
     */
    private $displayOnProductPage;
    /**
     * @var bool
     */
    private $showInProductListing;
    /**
     * @var bool
     */
    private $showInCartPage;
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
     * @var string[]
     */
    private $messages;
    /**
     * @var string[]
     */
    private $messagesBelowLimit;

    /**
     * @param bool $enabled
     * @param string|null $assetsKey
     * @param bool $displayOnProductPage
     * @param bool $showInProductListing
     * @param bool $showInCartPage
     * @param string $type
     * @param string $size
     * @param string $fontColor
     * @param string $backgroundColor
     * @param string $alignment
     * @param string $branding
     * @param string $startingText
     * @param string $amountFontSize
     * @param string $amountFontColor
     * @param string $amountFontBold
     * @param string $linkFontColor
     * @param string $linkUnderline
     * @param string $borderColor
     * @param string $borderRadius
     * @param string $noCostsClaim
     * @param string[] $messages
     * @param string[] $messagesBelowLimit
     */
    public function __construct(
        bool    $enabled,
        ?string $assetsKey,
        bool    $displayOnProductPage,
        bool    $showInProductListing,
        bool    $showInCartPage,
        string  $type = '',
        string  $size = '',
        string  $fontColor = '',
        string  $backgroundColor = '',
        string  $alignment = '',
        string  $branding = '',
        string  $startingText = '',
        string  $amountFontSize = '',
        string  $amountFontColor = '',
        string  $amountFontBold = '',
        string  $linkFontColor = '',
        string  $linkUnderline = '',
        string  $borderColor = '',
        string  $borderRadius = '',
        string  $noCostsClaim = '',
        array   $messages = [],
        array   $messagesBelowLimit = []
    )
    {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInProductListing = $showInProductListing;
        $this->showInCartPage = $showInCartPage;
        $this->type = $type;
        $this->size = $size;
        $this->fontColor = $fontColor;
        $this->backgroundColor = $backgroundColor;
        $this->alignment = $alignment;
        $this->branding = $branding;
        $this->startingText = $startingText;
        $this->amountFontSize = $amountFontSize;
        $this->amountFontColor = $amountFontColor;
        $this->amountFontBold = $amountFontBold;
        $this->linkFontColor = $linkFontColor;
        $this->linkUnderline = $linkUnderline;
        $this->borderColor = $borderColor;
        $this->borderRadius = $borderRadius;
        $this->noCostsClaim = $noCostsClaim;
        $this->messages = $messages;
        $this->messagesBelowLimit = $messagesBelowLimit;
    }

    /**
     * Transforms the request to a WidgetConfiguration object.
     *
     * @return WidgetSettings
     */
    public function transformToDomainModel(): object
    {
        return new WidgetSettings(
            $this->enabled,
            $this->displayOnProductPage,
            $this->showInProductListing,
            $this->showInCartPage,
            $this->assetsKey,
            new WidgetConfiguration(
                $this->type,
                $this->size,
                $this->fontColor,
                $this->backgroundColor,
                $this->alignment,
                $this->branding,
                $this->startingText,
                $this->amountFontSize,
                $this->amountFontColor,
                $this->amountFontBold,
                $this->linkFontColor,
                $this->linkUnderline,
                $this->borderColor,
                $this->borderRadius,
                $this->noCostsClaim
            ),
            new WidgetLabels(
                $this->messages,
                $this->messagesBelowLimit
            )
        );
    }
}
