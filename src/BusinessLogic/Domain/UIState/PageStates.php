<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState;

/**
 * Class PageState
 *
 * @package SeQura\Core\BusinessLogic\Domain\UIState
 */
class PageStates
{
    public const STATE_ONBOARDING = 'onboarding';
    public const STATE_DASHBOARD = 'dashboard';

    public const PAGE_CONNECT = 'connect';
    public const PAGE_SELLING_COUNTRIES = 'selling_countries';
    public const PAGE_WIDGETS = 'widgets';

    public const PAGE_GENERAL_SETTINGS = 'general_settings';
    public const PAGE_CONNECTION_SETTINGS = 'connection_settings';
    public const PAGE_ORDER_STATUS_SETTINGS = 'order_status_settings';
    public const PAGE_WIDGET_SETTINGS = 'widget_settings';

    /**
     * Possible onboarding pages.
     */
    public const ONBOARDING_PAGES = [
        self::PAGE_CONNECT,
        self::PAGE_SELLING_COUNTRIES,
        self::PAGE_WIDGETS
    ];

    /**
     * Possible dashboard pages.
     */
    public const DASHBOARD_PAGES = [
        self::PAGE_GENERAL_SETTINGS,
        self::PAGE_CONNECTION_SETTINGS,
        self::PAGE_ORDER_STATUS_SETTINGS,
        self::PAGE_WIDGET_SETTINGS
    ];
}
