<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;

/**
 * Class WidgetLabelsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses
 */
class WidgetLabelsResponse extends Response
{
    /**
     * @var WidgetLabels
     */
    private $widgetLabels;

    /**
     * @param WidgetLabels|null $widgetLabels
     */
    public function __construct(?WidgetLabels $widgetLabels)
    {
        $this->widgetLabels = $widgetLabels;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->widgetLabels) {
            return [];
        }

        return [
            'messages' => $this->widgetLabels->getMessages(),
            'messagesBelowLimit' => $this->widgetLabels->getMessagesBelowLimit(),
        ];
    }
}