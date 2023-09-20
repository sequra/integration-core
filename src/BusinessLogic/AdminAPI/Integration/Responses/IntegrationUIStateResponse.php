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
     * Connection string constant.
     */
    private const CONNECTION = 'connection';

    /**
     * Country configuration string constant.
     */
    private const COUNTRY_CONFIGURATION = 'country_configuration';

    /**
     * Widget configuration string constant.
     */
    private const WIDGET_CONFIGURATION = 'widget_configuration';

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
     * Called when user state is connection.
     *
     * @return IntegrationUIStateResponse
     */
    public static function connection(): self
    {
        return new self(self::CONNECTION);
    }

    /**
     * Called when user state is country configuration.
     *
     * @return IntegrationUIStateResponse
     */
    public static function countryConfiguration(): self
    {
        return new self(self::COUNTRY_CONFIGURATION);
    }

    /**
     * Called when user state is country configuration.
     *
     * @return IntegrationUIStateResponse
     */
    public static function widgetConfiguration(): self
    {
        return new self(self::WIDGET_CONFIGURATION);
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
