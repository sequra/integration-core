<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class ConnectionRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests
 */
class ConnectionRequest extends Request
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
     * @param string $environment
     * @param string $merchantId
     * @param string $username
     * @param string $password
     */
    public function __construct(string $environment, string $merchantId, string $username, string $password)
    {
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
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
            new AuthorizationCredentials(
                $this->username,
                $this->password
            )
        );
    }
}
