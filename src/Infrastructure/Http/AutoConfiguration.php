<?php

namespace SeQura\Core\Infrastructure\Http;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Exceptions\BaseException;

/**
 * Class AutoConfigurationController.
 *
 * @package SeQura\Core\Infrastructure\Http\Configuration
 */
class AutoConfiguration
{
    const CLASS_NAME = __CLASS__;

    /**
     * Process state: Not started.
     */
    const STATE_NOT_STARTED = 'not-started';
    /**
     * Process state: Started.
     */
    const STATE_STARTED = 'started';
    /**
     * Process state: Succeeded.
     */
    const STATE_SUCCEEDED = 'succeeded';
    /**
     * Process state: Failed.
     */
    const STATE_FAILED = 'failed';
    /**
     * @var Configuration
     */
    protected $configService;
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * AutoConfigurationController constructor.
     *
     * @param \SeQura\Core\Infrastructure\Configuration\Configuration $configService An instance of the configuration.
     * @param \SeQura\Core\Infrastructure\Http\HttpClient $httpClient An instance of the http client.
     */
    public function __construct(Configuration $configService, HttpClient $httpClient)
    {
        $this->configService = $configService;
        $this->httpClient = $httpClient;
    }

    /**
     * Starts the auto-configuration process.
     *
     * @return bool TRUE if the process completed successfully; otherwise, FALSE.
     *
     * @throws \SeQura\Core\Infrastructure\Exceptions\BaseException <p>When configuration service did not implement
     *  a method to return auto-configuration URL.</p>
     */
    public function start()
    {
        $this->configService->setAutoConfigurationState(self::STATE_STARTED);
        $url = $this->configService->getAutoConfigurationUrl();
        if (!$url) {
            throw new BaseException('Configuration service is not set to return auto-configuration URL');
        }

        $this->configService->setAsyncProcessCallHttpMethod(HttpClient::HTTP_METHOD_POST);
        $result = $this->httpClient->autoConfigure(HttpClient::HTTP_METHOD_POST, $url);

        if (!$result) {
            $result = $this->httpClient->autoConfigure(HttpClient::HTTP_METHOD_GET, $url);
            if ($result) {
                $this->configService->setAsyncProcessCallHttpMethod(HttpClient::HTTP_METHOD_GET);
            }
        }

        $this->configService->setAutoConfigurationState($result ? self::STATE_SUCCEEDED : self::STATE_FAILED);

        return $result;
    }

    /**
     * Retrieves the current auto-configuration state.
     *
     * @return string The current auto-configuration state.
     */
    public function getState()
    {
        return $this->configService->getAutoConfigurationState() ?: self::STATE_NOT_STARTED;
    }
}
