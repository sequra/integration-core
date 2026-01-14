<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class ReRegisterWebhookRequest.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests
 */
class ReRegisterWebhookRequest extends Request
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $deployment;

    /**
     * @param string $environment
     * @param string $merchantId
     * @param string $username
     * @param string $password
     * @param string $deployment
     */
    public function __construct(
        string $environment,
        string $merchantId,
        string $username,
        string $password,
        string $deployment
    ) {
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
        $this->deployment = $deployment;
    }

    /**
     * Transforms the request to a ConnectionData object.
     *
     * @return ConnectionData
     *
     * @throws InvalidEnvironmentException
     */
    public function transformToDomainModel(): object
    {
        return new ConnectionData(
            $this->environment,
            $this->merchantId,
            $this->deployment,
            new AuthorizationCredentials(
                $this->username,
                $this->password
            )
        );
    }
}
