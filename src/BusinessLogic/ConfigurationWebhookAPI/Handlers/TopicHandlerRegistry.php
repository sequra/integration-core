<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers;

use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\Singleton;

/**
 * Class TopicHandlerRegistry
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers
 */
class TopicHandlerRegistry extends Singleton
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * Map of registered topic handlers
     *
     * @var array<string, class-string<TopicHandlerInterface>>
     */
    protected $handlers = [];

    /**
     * Registers a handler for a specific topic.
     *
     * @param string $topic
     * @param class-string<TopicHandlerInterface> $handlerClass
     *
     * @return void
     */
    public static function register(string $topic, string $handlerClass): void
    {
        static::getInstance()->handlers[$topic] = $handlerClass;
    }

    /**
     * Gets a handler for the specified topic.
     *
     * @param string $topic
     *
     * @return TopicHandlerInterface|null
     */
    public static function getHandlerForTopic(string $topic): ?TopicHandlerInterface
    {
        return static::getInstance()->get($topic);
    }

    /**
     * Gets a handler instance for a topic.
     *
     * @param string $topic
     *
     * @return TopicHandlerInterface|null
     */
    protected function get(string $topic): ?TopicHandlerInterface
    {
        $handlerClass = $this->handlers[$topic] ?? null;

        if ($handlerClass === null) {
            return null;
        }

        return ServiceRegister::getService($handlerClass);
    }
}
