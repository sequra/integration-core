<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetLabels
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetLabels
{
    /**
     * Mini widget messages in supported languages.
     *
     * @var string[]
     */
    private $messages;
    /**
     * Mini widget messages for price below limit
     * in supported languages.
     *
     * @var string[]
     */
    private $messagesBelowLimit;

    /**
     * @param string[] $messages
     * @param string[] $messagesBelowLimit
     */
    public function __construct(array $messages = [], array $messagesBelowLimit = [])
    {
        $this->messages = $messages;
        $this->messagesBelowLimit = $messagesBelowLimit;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param  array $messages
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return string[]
     */
    public function getMessagesBelowLimit(): array
    {
        return $this->messagesBelowLimit;
    }

    /**
     * @param  array $messagesBelowLimit
     * @return void
     */
    public function setMessagesBelowLimit(array $messagesBelowLimit): void
    {
        $this->messagesBelowLimit = $messagesBelowLimit;
    }
}
