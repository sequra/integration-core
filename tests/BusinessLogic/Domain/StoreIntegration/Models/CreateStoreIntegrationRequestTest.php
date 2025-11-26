<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidWebhookUrlException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
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
     * @throws InvalidWebhookUrlException
     */
    public function testEmptyCapabilities(): void
    {
        // arrange
        $this->expectException(CapabilitiesEmptyException::class);
        // act

        new CreateStoreIntegrationRequest('merchant1', 'url', []);
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testInvalidWebhookUrl(): void
    {
        // arrange
        $this->expectException(InvalidWebhookUrlException::class);
        // act

        new CreateStoreIntegrationRequest('merchant1', 'url', [Capability::general(), Capability::advanced()]);
        // assert
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testValidCapabilities(): void
    {
        // arrange
        // act

        $request = new CreateStoreIntegrationRequest('merchant1', 'https://test.com', [Capability::general()]);

        // assert
        self::assertCount(1, $request->getCapabilities());
    }
}
