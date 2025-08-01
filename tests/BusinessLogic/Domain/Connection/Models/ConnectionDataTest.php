<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class ConnectionDataTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Connection
 */
class ConnectionDataTest extends BaseTestCase
{
    /**
     * @throws InvalidEnvironmentException
     */
    public function testConstructorAndGetters(): void
    {
        $environment = BaseProxy::TEST_MODE;
        $merchantId = 'test_merchant';
        $authorizationCredentials = new AuthorizationCredentials('test_username', 'test_password');
        $deployment = 'sequra';

        $connectionData = new ConnectionData($environment, $merchantId, $deployment, $authorizationCredentials);

        $this->assertSame($environment, $connectionData->getEnvironment());
        $this->assertSame($merchantId, $connectionData->getMerchantId());
        $this->assertSame($authorizationCredentials, $connectionData->getAuthorizationCredentials());
        $this->assertSame($deployment, $connectionData->getDeployment());
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testSetters(): void
    {
        $environment = BaseProxy::TEST_MODE;
        $merchantId = 'test_merchant';
        $authorizationCredentials = new AuthorizationCredentials('test_username', 'test_password');
        $deployment = 'sequra';

        $connectionData = new ConnectionData($environment, $merchantId, $deployment, $authorizationCredentials);

        $newEnvironment = BaseProxy::LIVE_MODE;
        $newMerchantId = 'new_test_merchant';
        $newAuthorizationCredentials = new AuthorizationCredentials('test_username', 'test_password');
        $newDeployment = 'svea';

        $connectionData->setEnvironment($newEnvironment);
        $connectionData->setMerchantId($newMerchantId);
        $connectionData->setAuthorizationCredentials($newAuthorizationCredentials);
        $connectionData->setDeployment($newDeployment);

        $this->assertSame($newEnvironment, $connectionData->getEnvironment());
        $this->assertSame($newMerchantId, $connectionData->getMerchantId());
        $this->assertSame($newAuthorizationCredentials, $connectionData->getAuthorizationCredentials());
        $this->assertSame($newDeployment, $connectionData->getDeployment());
    }

    public function testInvalidEnvironmentException(): void
    {
        $this->expectException(InvalidEnvironmentException::class);
        $this->expectExceptionMessage('Invalid environment type.');

        $environment = 'invalid_env';
        $merchantId = 'test_merchant';
        $deployment = 'sequra';
        $authorizationCredentials = new AuthorizationCredentials('test_username', 'test_password');

        new ConnectionData($environment, $merchantId, $deployment, $authorizationCredentials);
    }
}
