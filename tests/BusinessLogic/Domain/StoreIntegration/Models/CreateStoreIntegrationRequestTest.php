<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CreateStoreIntegrationRequestTest.
 *
 * @package Domain\StoreIntegration\Models
 */
class CreateStoreIntegrationRequestTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testEmptyCapabilities(): void
    {
        // arrange
        $this->expectException(CapabilitiesEmptyException::class);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        // act

        new CreateStoreIntegrationRequest($connectionData, new URL('https://test.com'), []);
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testInvalidWebhookUrl(): void
    {
        // arrange
        $this->expectException(InvalidUrlException::class);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        // act

        new CreateStoreIntegrationRequest(
            $connectionData,
            new URL('url'),
            [Capability::general(), Capability::advanced()]
        );
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidEnvironmentException
     */
    public function testValidCapabilities(): void
    {
        // arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        // act

        $request = new CreateStoreIntegrationRequest($connectionData, new URL('https://test.com'), [Capability::general()]);

        // assert
        self::assertCount(1, $request->getCapabilities());
    }
}
