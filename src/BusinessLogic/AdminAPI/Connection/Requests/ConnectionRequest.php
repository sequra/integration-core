<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests;

/**
 * Class ConnectionRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests
 */
class ConnectionRequest
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

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
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
