<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\TranslatableErrorResponse;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class TopicMissingErrorResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses
 */
class TopicMissingErrorResponse extends TranslatableErrorResponse
{
    public function __construct()
    {
        parent::__construct(new BaseTranslatableException(
            new TranslatableLabel(
                'Topic field is required in the webhook payload.',
                'TOPIC_MISSING'
            )
        ));
    }
}
