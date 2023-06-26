<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\UIState\Models\UIPageState;

/**
 * Class IntegrationUIStateResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationUIStateResponse extends Response
{
    /**
     * @var UIPageState
     */
    private $UIState;

    /**
     * @param UIPageState $UIState
     */
    public function __construct(UIPageState $UIState)
    {
        $this->UIState = $UIState;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'state' => $this->UIState->getState(),
            'page' => $this->UIState->getPage()
        ];
    }
}
