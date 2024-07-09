<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class EventsWebhook
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class EventsWebhook extends OrderRequestDTO
{
    /**
     * @var string SeQura will make a POST to this URL when an event happens for that order after confirmation.
     */
    protected $url;

    /**
     * @var string[]|null Optional name/value pairs that will be included in the webhook event POST.
     */
    protected $parameters;

    /**
     * @param string $url
     * @param string[]|null $parameters
     *
     * @throws InvalidUrlException
     */
    public function __construct(string $url, array $parameters = null)
    {
        if (!StringValidator::isValidUrl($url)) {
            throw new InvalidUrlException('Url must be a valid url.');
        }

        $this->url = $url;
        $this->parameters = $parameters;
    }

    /**
     * Create a new EventsWebhook instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return EventsWebhook Returns a new EventsWebhook instance.
     * @throws InvalidUrlException
     */
    public static function fromArray(array $data): EventsWebhook
    {
        return new self(
            self::getDataValue($data, 'url'),
            self::getDataValue($data, 'parameters', null)
        );
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string[]|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
