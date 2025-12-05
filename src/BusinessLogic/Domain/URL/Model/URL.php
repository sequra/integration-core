<?php

namespace SeQura\Core\BusinessLogic\Domain\URL\Model;

use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;

/**
 * Class URL.
 *
 * @package SeQura\Core\BusinessLogic\Domain\URL\Model
 */
class URL
{
    /**
     * @var string $path
     */
    private $path;

    /**
     * @var Query[] $queries
     */
    private $queries;

    /**
     * @param string $path
     * @param Query[] $queries
     *
     * @throws InvalidUrlException
     */
    public function __construct(string $path, array $queries = [])
    {
        $this->validatePath($path);

        $this->path = $path;
        $this->queries = $queries;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Query[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @param Query $query
     *
     * @return void
     */
    public function addQuery(Query $query): void
    {
        $this->queries[] = $query;
    }

    /**
     * @return string
     */
    public function buildUrl(): string
    {
        if (empty($this->queries)) {
            return $this->path;
        }

        $queryParams = [];

        foreach ($this->queries as $query) {
            $queryParams[] = urlencode($query->getKey()) . '=' . urlencode($query->getValue());
        }

        return $this->path . '?' . implode('&', $queryParams);
    }

    /**
     * @param string $path
     *
     * @return void
     *
     * @throws InvalidUrlException
     */
    public function validatePath(string $path): void
    {
        if (filter_var($path, FILTER_VALIDATE_URL) === false) {
            throw new InvalidUrlException();
        }
    }

    /**
     * @param string $key
     *
     * @return ?Query
     */
    public function getQueryByKey(string $key): ?Query
    {
        $matches = array_filter(
            $this->queries,
            static function (Query $q) use ($key) {
                return $q->getKey() === $key;
            }
        );

        return reset($matches) ?: null;
    }
}
