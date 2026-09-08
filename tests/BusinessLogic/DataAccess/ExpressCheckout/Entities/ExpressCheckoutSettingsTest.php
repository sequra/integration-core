<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\ExpressCheckout\Entities;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;

/**
 * Class ExpressCheckoutSettingsTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\ExpressCheckout\Entities
 */
class ExpressCheckoutSettingsTest extends TestCase
{
    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateDropsAStoredButtonStyleThatNoLongerValidates(): void
    {
        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 1,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [
                    ['page' => 'product', 'enabled' => true],
                ],
                'buttonStyle' => '<div>',
            ],
        ]);

        // Assert
        $settings = $entity->getExpressCheckoutSettings();
        self::assertNull($settings->getButtonStyle());
        self::assertTrue($settings->isPageEnabled('product'));
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateKeepsAValidStoredButtonStyle(): void
    {
        // Arrange
        $buttonStyle = '{"backgroundColor":"#123456"}';

        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 1,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [
                    ['page' => 'product', 'enabled' => true],
                ],
                'buttonStyle' => $buttonStyle,
            ],
        ]);

        // Assert
        self::assertSame($buttonStyle, $entity->getExpressCheckoutSettings()->getButtonStyle());
    }
}
