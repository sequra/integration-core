<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\ExpressCheckout\Entities;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings as DomainExpressCheckoutSettings;

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

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testToArrayAndInflateRoundTripTheTermsAndConditionsUrl(): void
    {
        // Arrange
        $entity = new ExpressCheckoutSettings();
        $entity->setStoreId('1');
        $entity->setExpressCheckoutSettings(new DomainExpressCheckoutSettings(
            [new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true)],
            '{"backgroundColor":"#123456"}',
            'https://shop.example.com/terms'
        ));

        // Act
        $restored = ExpressCheckoutSettings::fromArray($entity->toArray());

        // Assert
        $settings = $restored->getExpressCheckoutSettings();
        self::assertSame('https://shop.example.com/terms', $settings->getTermsAndConditionsUrl());
        self::assertSame('{"backgroundColor":"#123456"}', $settings->getButtonStyle());
        self::assertTrue($settings->isPageEnabled('product'));
        self::assertSame('1', $restored->getStoreId());
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateLoadsALegacyRowWithoutTheTermsAndConditionsUrl(): void
    {
        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 7,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [['page' => 'cart', 'enabled' => true]],
                'buttonStyle' => null,
            ],
        ]);

        // Assert
        $settings = $entity->getExpressCheckoutSettings();
        self::assertNull($settings->getTermsAndConditionsUrl());
        self::assertTrue($settings->isPageEnabled('cart'));
    }

    /**
     * @return array<string, mixed[]>
     */
    public function unusableStoredTermsAndConditionsUrlProvider(): array
    {
        return [
            'invalid url' => ['javascript:alert(1)'],
            'non string' => [['https://shop.example.com/terms']],
        ];
    }

    /**
     * @dataProvider unusableStoredTermsAndConditionsUrlProvider
     *
     * @param mixed $storedUrl
     *
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateDropsAnUnusableStoredTermsAndConditionsUrl($storedUrl): void
    {
        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 1,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [['page' => 'cart', 'enabled' => true]],
                'buttonStyle' => '{"backgroundColor":"#123456"}',
                'termsAndConditionsUrl' => $storedUrl,
            ],
        ]);

        // Assert
        $settings = $entity->getExpressCheckoutSettings();
        self::assertNull($settings->getTermsAndConditionsUrl());
        self::assertSame('{"backgroundColor":"#123456"}', $settings->getButtonStyle());
        self::assertTrue($settings->isPageEnabled('cart'));
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateKeepsAValidStoredUrlWhenTheButtonStyleIsDropped(): void
    {
        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 1,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [['page' => 'cart', 'enabled' => true]],
                'buttonStyle' => '<div>',
                'termsAndConditionsUrl' => 'https://shop.example.com/terms',
            ],
        ]);

        // Assert
        $settings = $entity->getExpressCheckoutSettings();
        self::assertNull($settings->getButtonStyle());
        self::assertSame('https://shop.example.com/terms', $settings->getTermsAndConditionsUrl());
        self::assertTrue($settings->isPageEnabled('cart'));
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testInflateDropsBothAnInvalidButtonStyleAndAnInvalidUrlButKeepsTheConfigs(): void
    {
        // Act
        $entity = ExpressCheckoutSettings::fromArray([
            'id' => 1,
            'storeId' => '1',
            'expressCheckoutSettings' => [
                'expressCheckoutConfigs' => [['page' => 'cart', 'enabled' => true]],
                'buttonStyle' => '<div>',
                'termsAndConditionsUrl' => 'ftp://shop.example.com/terms',
            ],
        ]);

        // Assert
        $settings = $entity->getExpressCheckoutSettings();
        self::assertNull($settings->getButtonStyle());
        self::assertNull($settings->getTermsAndConditionsUrl());
        self::assertTrue($settings->isPageEnabled('cart'));
    }
}
