<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;

/**
 * Class MockMiniWidgetMessagesProvider.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockMiniWidgetMessagesProvider implements MiniWidgetMessagesProviderInterface
{
    /**
     * @var null|string
     */
    private $message = null;
    /**
     * @var null|string
     */
    private $belowLimitMessage = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getBelowLimitMessage(): ?string
    {
        return $this->belowLimitMessage;
    }

    /**
     * @param string|null $message
     */
    public function setMockMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string|null $belowLimitMessage
     */
    public function setMockBelowLimitMessage(?string $belowLimitMessage): void
    {
        $this->belowLimitMessage = $belowLimitMessage;
    }
}
