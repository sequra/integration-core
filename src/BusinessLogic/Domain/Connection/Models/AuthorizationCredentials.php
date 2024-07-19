<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class AuthorizationCredentials
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Authorization
 */
class AuthorizationCredentials extends DataTransferObject
{
    /**
     * @var string Authorization username.
     */
    protected $username;

    /**
     * @var string Authorization username.
     */
    protected $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['username'] = $this->username;
        $data['password'] = $this->password;

        return $data;
    }
}
