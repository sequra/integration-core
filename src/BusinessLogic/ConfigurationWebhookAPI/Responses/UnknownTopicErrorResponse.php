<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\TranslatableErrorResponse;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class UnknownTopicErrorResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses
 */
class UnknownTopicErrorResponse extends TranslatableErrorResponse
{
    /**
     * @param string $topic
     */
    public function __construct(string $topic)
    {
        $safeTopic = htmlspecialchars($topic, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        parent::__construct(new BaseTranslatableException(
            new TranslatableLabel(
                "Unknown or unsupported topic: {$safeTopic}",
                'UNKNOWN_TOPIC'
            )
        ));
    }
}
