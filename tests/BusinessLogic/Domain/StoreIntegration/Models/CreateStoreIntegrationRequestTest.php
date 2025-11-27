<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
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
     */
    public function testEmptyCapabilities(): void
    {
        // arrange
        $this->expectException(CapabilitiesEmptyException::class);
        // act

        new CreateStoreIntegrationRequest('merchant1', new URL('https://test.com'), []);
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     */
    public function testInvalidWebhookUrl(): void
    {
        // arrange
        $this->expectException(InvalidUrlException::class);
        // act

        new CreateStoreIntegrationRequest('merchant1', new URL('url'), [Capability::general(), Capability::advanced()]);
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     */
    public function testValidCapabilities(): void
    {
        // arrange
        // act

        $request = new CreateStoreIntegrationRequest('merchant1', new URL('https://test.com'), [Capability::general()]);

        // assert
        self::assertCount(1, $request->getCapabilities());
    }
}
