<?php

/** @noinspection PhpMissingDocCommentInspection */

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents;

use SeQura\Core\Infrastructure\Http\CurlHttpClient;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use SeQura\Core\Infrastructure\Http\HttpResponse;

class TestCurlHttpClient extends CurlHttpClient
{
    public const REQUEST_TYPE_SYNCHRONOUS = 1;
    public const REQUEST_TYPE_ASYNCHRONOUS = 2;
    public const MAX_REDIRECTS = 5;
    public $setAdditionalOptionsCallHistory = array();
    /**
     * @var array
     */
    private $responses;
    /**
     * @var array
     */
    private $history;

    /**
     * Set all mock responses.
     *
     * @param array $responses
     */
    public function setMockResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    /**
     * Return call history.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Gets cURL options set for the request.
     *
     * @return array Curl options.
     */
    public function getCurlOptions(): array
    {
        return $this->curlOptions;
    }

    /**
     * Sets indicator whether to follow location or not.
     *
     * @param bool $follow
     */
    public function setFollowLocation(bool $follow): void
    {
        $this->followLocation = $follow;
    }

    /**
     * @inheritdoc
     */
    protected function executeSynchronousRequest(): HttpResponse
    {
        $this->setHistory(self::REQUEST_TYPE_SYNCHRONOUS);

        return parent::executeSynchronousRequest();
    }

    /**
     * @inheritdoc
     */
    protected function executeAsynchronousRequest(): void
    {
        $this->setHistory(self::REQUEST_TYPE_ASYNCHRONOUS);
    }

    /**
     * Mocks cURL request and returns response and status code.
     *
     * @return array Array with plain response as the first item and status code as the second item.
     *
     * @throws HttpCommunicationException
     */
    protected function executeCurlRequest(): array
    {
        if (empty($this->responses)) {
            throw new HttpCommunicationException('No response');
        }

        $response = array_shift($this->responses);

        $headers = !empty($response['headers']) ? $response['headers'] : array();
        return array($response['data'], $response['status'], $headers);
    }

    /**
     * @inheritdoc
     */
    protected function setAdditionalOptions(string $domain, array $options): void
    {
        parent::setAdditionalOptions($domain, $options);
        $this->setAdditionalOptionsCallHistory[$domain][] = $options;
    }

    /**
     * Sets call history.
     *
     * @param int $type
     */
    protected function setHistory(int $type): void
    {
        $this->history[] = array(
            'type' => $type,
            'method' => $this->curlOptions[CURLOPT_CUSTOMREQUEST] ?? 'POST',
            'url' => $this->curlOptions[CURLOPT_URL],
            'headers' => $this->curlOptions[CURLOPT_HTTPHEADER],
            'body' => $this->curlOptions[CURLOPT_POSTFIELDS] ?? '',
        );
    }
}
