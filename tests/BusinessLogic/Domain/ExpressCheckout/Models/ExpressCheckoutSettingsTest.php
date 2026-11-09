<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutButtonStyleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidTermsAndConditionsUrlException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use stdClass;

/**
 * Class ExpressCheckoutSettingsTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutSettingsTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructorAcceptsEmptyArrayByDefault(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertSame([], $settings->getExpressCheckoutConfigs());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorStoresValidConfigsInOrder(): void
    {
        $product = new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true);
        $cart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false);
        $miniCart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::miniCart(), true);

        $settings = new ExpressCheckoutSettings([$product, $cart, $miniCart]);

        self::assertSame([$product, $cart, $miniCart], $settings->getExpressCheckoutConfigs());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorReindexesAssociativeKeys(): void
    {
        $product = new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true);
        $cart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false);

        $settings = new ExpressCheckoutSettings(['a' => $product, 'b' => $cart]);

        self::assertSame([0, 1], array_keys($settings->getExpressCheckoutConfigs()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     */
    public function testConstructorThrowsWhenAnEntryIsNotAPageConfig(): void
    {
        $this->expectException(InvalidExpressCheckoutPageConfigException::class);

        new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new stdClass(),
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorThrowsOnDuplicatePages(): void
    {
        $this->expectException(DuplicatedExpressCheckoutPageException::class);

        new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), false),
        ]);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsTrueForEnabledPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
        ]);

        self::assertTrue($settings->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsFalseForDisabledPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
        ]);

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::cart()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsFalseForUnknownPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::miniCart()->getPage()));
    }

    /**
     * @return void
     */
    public function testIsPageEnabledReturnsFalseWhenNoConfigsStored(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testToArraySerializesConfigs(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::miniCart(), true),
        ]);

        self::assertSame([
            'expressCheckoutConfigs' => [
                ['page' => 'product', 'enabled' => true],
                ['page' => 'cart', 'enabled' => false],
                ['page' => 'mini-cart', 'enabled' => true],
            ],
            'buttonStyle' => null,
            'termsAndConditionsUrl' => null,
        ], $settings->toArray());
    }

    /**
     * @return void
     */
    public function testToArrayReturnsEmptyConfigsForDefaultConstruction(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertSame(
            ['expressCheckoutConfigs' => [], 'buttonStyle' => null, 'termsAndConditionsUrl' => null],
            $settings->toArray()
        );
    }

    /**
     * @return void
     */
    public function testButtonStyleDefaultsToNull(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertNull($settings->getButtonStyle());
    }

    /**
     * @return void
     */
    public function testConstructorTreatsAnEmptyButtonStyleAsUnset(): void
    {
        $settings = new ExpressCheckoutSettings([], '');

        self::assertNull($settings->getButtonStyle());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testButtonStyleIsCarriedVerbatim(): void
    {
        $buttonStyle = '{"backgroundColor":"#123456","somethingNewerThanThisRelease":true}';

        $settings = new ExpressCheckoutSettings(
            [new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true)],
            $buttonStyle
        );

        self::assertSame($buttonStyle, $settings->getButtonStyle());
        self::assertSame($buttonStyle, $settings->toArray()['buttonStyle']);
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidButtonStyleProvider(): array
    {
        return [
            'json null literal' => ['null'],
            'json number' => ['123'],
            'json string' => ['"#ffffff"'],
            'json list' => ['[{"backgroundColor":"#123456"}]'],
            'truncated object' => ['{"backgroundColor":'],
            'not json at all' => ['<div>'],
        ];
    }

    /**
     * @dataProvider invalidButtonStyleProvider
     *
     * @param string $buttonStyle
     *
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     */
    public function testConstructorRejectsAButtonStyleThatIsNotAJsonObject(string $buttonStyle): void
    {
        $this->expectException(InvalidExpressCheckoutButtonStyleException::class);

        new ExpressCheckoutSettings([], $buttonStyle);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     */
    public function testConstructorRejectsAnOversizedButtonStyle(): void
    {
        $buttonStyle = '{"backgroundColor":"' . str_repeat('a', 1515) . '"}';
        self::assertSame(1537, strlen($buttonStyle));

        $this->expectException(InvalidExpressCheckoutButtonStyleException::class);

        new ExpressCheckoutSettings([], $buttonStyle);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     */
    public function testConstructorAcceptsAButtonStyleAtTheSizeLimit(): void
    {
        $buttonStyle = '{"backgroundColor":"' . str_repeat('a', 1514) . '"}';
        self::assertSame(1536, strlen($buttonStyle));

        $settings = new ExpressCheckoutSettings([], $buttonStyle);

        self::assertSame($buttonStyle, $settings->getButtonStyle());
    }

    /**
     * @return array<string, string[]>
     */
    public function validTermsAndConditionsUrlProvider(): array
    {
        return [
            'https' => ['https://shop.example.com/terms'],
            'http with query and fragment' => ['http://shop.example.com/terms?lang=es#top'],
            'uppercase scheme' => ['HTTPS://shop.example.com/terms'],
            'idn path' => ['https://tienda.es/términos-y-condiciones'],
            'idn host' => ['https://españa-shop.es/terms'],
            'port' => ['https://shop.example.com:8443/terms'],
            'port 0' => ['https://shop.example.com:0/terms'],
            'highest port' => ['https://shop.example.com:65535/terms'],
            'empty port' => ['https://shop.example.com:/terms'],
            'ipv6' => ['https://[2001:db8::1]/terms'],
            'uppercase ipv6 with port' => ['https://[2001:DB8::1]:8443/terms'],
            'ipv6 loopback' => ['https://[::1]/terms'],
            'ipv4 mapped ipv6' => ['https://[::ffff:192.0.2.1]/terms'],
            'ipv4' => ['https://192.168.1.10/terms'],
            'idn host and path' => ['https://tienda-ñ.es/términos'],
            'user info' => ['https://user:pass@shop.example.com/terms'],
            'punycode host' => ['https://xn--espaa-shop-9db.es/terms'],
        ];
    }

    /**
     * @dataProvider validTermsAndConditionsUrlProvider
     *
     * @param string $url
     *
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     * @throws InvalidTermsAndConditionsUrlException
     */
    public function testConstructorAcceptsAnAbsoluteHttpTermsAndConditionsUrl(string $url): void
    {
        $settings = new ExpressCheckoutSettings([], null, $url);

        self::assertSame($url, $settings->getTermsAndConditionsUrl());
        self::assertSame($url, $settings->toArray()['termsAndConditionsUrl']);
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidTermsAndConditionsUrlProvider(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:text/html,<b>x</b>'],
            'ftp scheme' => ['ftp://shop.example.com/terms'],
            'mailto scheme' => ['mailto:legal@shop.example.com'],
            'relative path' => ['/terms'],
            'scheme only' => ['https://'],
            'empty host' => ['https:///terms'],
            'port without host' => ['https://:443/terms'],
            'user info without host' => ['https://user@/terms'],
            'javascript with host' => ['javascript://shop.example.com/%0Aalert(1)'],
            'scheme relative' => ['//shop.example.com/terms'],
            'space' => ['https://shop.example.com/my terms'],
            'newline' => ["https://shop.example.com/te\nrms"],
            'null byte' => ["https://shop.example.com/\0terms"],
            'percent host' => ['https://%/terms'],
            'underscore host' => ['https://shop_example.com/terms'],
            'port overflow' => ['https://shop.example.com:65536/terms'],
            'six digit port' => ['https://shop.example.com:123456/terms'],
            'empty label' => ['https://a..b/x'],
            'leading hyphen' => ['https://-a.com/terms'],
            'trailing hyphen' => ['https://a-.com/terms'],
            'trailing dot' => ['https://shop.example.com./terms'],
            'hostname in brackets' => ['https://[shop.example.com]/terms'],
            'single char ipv6' => ['https://[a]/terms'],
            'short ipv6' => ['https://[1:2:3]/terms'],
            'non hex ipv6' => ['https://[::g]/terms'],
            'double compression ipv6' => ['https://[1::2::3]/terms'],
            'ipv6 zone id' => ['https://[2001:db8::1%25eth0]/terms'],
            'non breaking space in path' => ["https://shop.example.com/my\u{00A0}terms"],
            'non breaking space in host' => ["https://shop\u{00A0}example.com/terms"],
        ];
    }

    /**
     * @dataProvider invalidTermsAndConditionsUrlProvider
     *
     * @param string $url
     *
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     */
    public function testConstructorRejectsAnythingButAnAbsoluteHttpTermsAndConditionsUrl(string $url): void
    {
        $this->expectException(InvalidTermsAndConditionsUrlException::class);
        $this->expectExceptionCode(400);

        new ExpressCheckoutSettings([], null, $url);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     * @throws InvalidTermsAndConditionsUrlException
     */
    public function testConstructorStoresTheTermsAndConditionsUrlTrimmed(): void
    {
        $settings = new ExpressCheckoutSettings([], null, "  https://shop.example.com/terms \t");

        self::assertSame('https://shop.example.com/terms', $settings->getTermsAndConditionsUrl());
    }

    /**
     * @return array<string, array<string|null>>
     */
    public function unsetTermsAndConditionsUrlProvider(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'whitespace only' => ["  \t"],
        ];
    }

    /**
     * @dataProvider unsetTermsAndConditionsUrlProvider
     *
     * @param string|null $url
     *
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     * @throws InvalidTermsAndConditionsUrlException
     */
    public function testConstructorTreatsAnEmptyTermsAndConditionsUrlAsUnset(?string $url): void
    {
        $settings = new ExpressCheckoutSettings([], null, $url);

        self::assertNull($settings->getTermsAndConditionsUrl());
        self::assertNull($settings->toArray()['termsAndConditionsUrl']);
    }

    /**
     * @return void
     */
    public function testInvalidTermsAndConditionsUrlIsATranslatableBadRequest(): void
    {
        $exception = new InvalidTermsAndConditionsUrlException();

        self::assertSame(400, $exception->getCode());
        self::assertSame(
            'general.errors.expressCheckout.invalidTermsAndConditionsUrl',
            $exception->getTranslatableLabel()->getCode()
        );
    }
}
