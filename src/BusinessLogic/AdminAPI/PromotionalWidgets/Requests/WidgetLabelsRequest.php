<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;

/**
 * Class WidgetLabelsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests
 */
class WidgetLabelsRequest extends Request
{
    /**
     * @var string[]
     */
    private $messages;
    /**
     * @var string[]
     */
    private $messagesBelowLimit;

    /**
     * @param string[] $messages
     * @param string[] $messagesBelowLimit
     */
    public function __construct(array $messages, array $messagesBelowLimit)
    {
        $this->messages = $messages;
        $this->messagesBelowLimit = $messagesBelowLimit;
    }

    /**
     * @inheritDoc
     */
    public function transformToDomainModel()
    {
        return new WidgetLabels($this->messages, $this->messagesBelowLimit);
    }
}