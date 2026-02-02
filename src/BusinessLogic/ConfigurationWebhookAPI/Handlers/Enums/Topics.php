<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Enums;

/**
 * Interface Topics.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers
 */
interface Topics
{
    /**
     * @var string
     */
    public const GET_GENERAL_SETTINGS = 'get-general-settings';
    /**
     * @var string
     */
    public const SAVE_GENERAL_SETTINGS = 'save-general-settings';
    /**
     * @var string
     */
    public const GET_WIDGET_SETTINGS = 'get-widget-settings';
    /**
     * @var string
     */
    public const SAVE_WIDGET_SETTINGS = 'save-widget-settings';
    /**
     * @var string
     */
    public const GET_ORDER_STATUS_LIST = 'get-order-status-list';
    /**
     * @var string
     */
    public const GET_ORDER_STATUS_SETTINGS = 'get-order-status-settings';
    /**
     * @var string
     */
    public const SAVE_ORDER_STATUS_SETTINGS = 'save-order-status-settings';
    /**
     * @var string
     */
    public const GET_ADVANCED_SETTINGS = 'get-advanced-settings';
    /**
     * @var string
     */
    public const SAVE_ADVANCED_SETTINGS = 'save-advanced-settings';
    /**
     * @var string
     */
    public const GET_LOG_CONTENT = 'get-log-content';
    /**
     * @var string
     */
    public const REMOVE_LOG_CONTENT = 'remove-log-content';
    /**
     * @var string
     */
    public const GET_SHOP_CATEGORIES = 'get-shop-categories';
    /**
     * @var string
     */
    public const GET_SHOP_PRODUCTS = 'get-shop-products';
    /**
     * @var string
     */
    public const GET_SELLING_COUNTRIES = 'get-selling-countries';
    /**
     * @var string
     */
    public const GET_STORE_INFO = 'get-store-info';
    /**
     * @var string[]
     */
    public const ALL_TOPICS = [
        self::GET_GENERAL_SETTINGS,
        self::SAVE_GENERAL_SETTINGS,
        self::GET_WIDGET_SETTINGS,
        self::SAVE_WIDGET_SETTINGS,
        self::GET_ORDER_STATUS_LIST,
        self::GET_ORDER_STATUS_SETTINGS,
        self::SAVE_ORDER_STATUS_SETTINGS,
        self::GET_ADVANCED_SETTINGS,
        self::SAVE_ADVANCED_SETTINGS,
        self::GET_LOG_CONTENT,
        self::REMOVE_LOG_CONTENT,
        self::GET_SHOP_CATEGORIES,
        self::GET_SHOP_PRODUCTS,
        self::GET_SELLING_COUNTRIES,
        self::GET_STORE_INFO
    ];
}
