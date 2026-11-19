<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests;

/**
 * Class PaginationRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests
 */
class PaginationRequest extends ConfigurationWebhookRequest
{
    /**
     * @var int $page
     */
    private $page;
    /**
     * @var int $limit
     */
    private $limit;
    /**
     * @var string $search
     */
    private $search;

    /**
     * @param int $page
     * @param int $limit
     * @param string $search
     */
    public function __construct(int $page, int $limit, string $search)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->search = $search;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self(
            $payload['page'] ?? 1,
            $payload['limit'] ?? 10,
            $payload['search'] ?? ''
        );
    }
}
