<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class IntegrationUIStateResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationUIStateResponse extends Response
{
    /**
     * Onboarding string constant.
     */
    private const ONBOARDING = 'onboarding';

    /**
     * Dashboard string constant.
     */
    private const DASHBOARD = 'dashboard';

    /**
     * String representation of state.
     *
     * @var string
     */
    private $state;

    /**
     * @param string $state
     */
    private function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * Called when user state is onboarding.
     *
     * @return IntegrationUIStateResponse
     */
    public static function onboarding(): self
    {
        return new self(self::ONBOARDING);
    }

    /**
     * Called when user state is dashboard.
     *
     * @return IntegrationUIStateResponse
     */
    public static function dashboard(): self
    {
        return new self(self::DASHBOARD);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state
        ];
    }
}
