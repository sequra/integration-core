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
    protected const ONBOARDING = 'onboarding';

    /**
     * Dashboard string constant.
     */
    protected const DASHBOARD = 'dashboard';

    /**
     * String representation of state.
     *
     * @var string
     */
    protected $state;

    /**
     * @param string $state
     */
    protected function __construct(string $state)
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
