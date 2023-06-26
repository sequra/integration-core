<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\UIState\Models\UIPages;

/**
 * Class IntegrationAvailableUIStatesResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationAvailableUIPagesResponse extends Response
{
    /**
     * @var UIPages
     */
    private $UIPages;

    /**
     * @param UIPages $UIPages
     */
    public function __construct(UIPages $UIPages)
    {
        $this->UIPages = $UIPages;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'onboarding' => $this->UIPages->getOnboardingPages(),
            'dashboard' => $this->UIPages->getDashboardPages()
        ];
    }
}
