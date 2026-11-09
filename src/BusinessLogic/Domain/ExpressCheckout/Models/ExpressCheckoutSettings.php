<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutButtonStyleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidTermsAndConditionsUrlException;

/**
 * Class ExpressCheckoutSettings
 *
 * Aggregate of per-page Express Checkout configurations for a single store.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutSettings
{
    /**
     * The style is an opaque blob: the keys it may carry are owned by the
     * merchant portal and the express checkout button page, so that neither
     * needs a release of this library to grow. Only its shape is checked here.
     * The size ceiling is what the button page's URL query parameter can carry
     * (integration-assets drops anything larger), and it also keeps an oversized
     * style from truncating the serialized column it shares with the page
     * configs.
     */
    private const BUTTON_STYLE_MAX_BYTES = 1536;

    /**
     * @var ExpressCheckoutPageConfig[]
     */
    protected $expressCheckoutConfigs;

    /**
     * @var string|null
     */
    protected $buttonStyle;

    /**
     * @var string|null
     */
    protected $termsAndConditionsUrl;

    /**
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     * @param string|null $buttonStyle
     * @param string|null $termsAndConditionsUrl
     *
     * @throws InvalidExpressCheckoutPageConfigException When an entry is not an ExpressCheckoutPageConfig.
     * @throws DuplicatedExpressCheckoutPageException When two entries reference the same page.
     * @throws InvalidExpressCheckoutButtonStyleException When the style is not a JSON object within the size limit.
     * @throws InvalidTermsAndConditionsUrlException When the URL is not an absolute http or https URL.
     */
    public function __construct(
        array $expressCheckoutConfigs = [],
        ?string $buttonStyle = null,
        ?string $termsAndConditionsUrl = null
    ) {
        $this->validateConfigs($expressCheckoutConfigs);
        $this->expressCheckoutConfigs = array_values($expressCheckoutConfigs);
        $this->buttonStyle = $this->normalizeButtonStyle($buttonStyle);
        $this->termsAndConditionsUrl = $this->normalizeTermsAndConditionsUrl($termsAndConditionsUrl);
    }

    /**
     * @return ExpressCheckoutPageConfig[]
     */
    public function getExpressCheckoutConfigs(): array
    {
        return $this->expressCheckoutConfigs;
    }

    /**
     * @return string|null
     */
    public function getButtonStyle(): ?string
    {
        return $this->buttonStyle;
    }

    /**
     * @return string|null
     */
    public function getTermsAndConditionsUrl(): ?string
    {
        return $this->termsAndConditionsUrl;
    }

    /**
     * @param string $page Page identifier string (see ExpressCheckoutPage factories).
     *
     * @return bool
     */
    public function isPageEnabled(string $page): bool
    {
        foreach ($this->expressCheckoutConfigs as $config) {
            if ($config->getPage()->getPage() === $page) {
                return $config->isEnabled();
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expressCheckoutConfigs' => array_map(static function (ExpressCheckoutPageConfig $config) {
                return $config->toArray();
            }, $this->expressCheckoutConfigs),
            'buttonStyle' => $this->buttonStyle,
            'termsAndConditionsUrl' => $this->termsAndConditionsUrl,
        ];
    }

    /**
     * Asserts that each entry is an ExpressCheckoutPageConfig and that no two
     * entries reference the same page.
     *
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     *
     * @return void
     *
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws DuplicatedExpressCheckoutPageException
     */
    private function validateConfigs(array $expressCheckoutConfigs): void
    {
        $seenPages = [];

        foreach ($expressCheckoutConfigs as $config) {
            if (!$config instanceof ExpressCheckoutPageConfig) {
                throw new InvalidExpressCheckoutPageConfigException();
            }

            $page = $config->getPage()->getPage();
            if (\in_array($page, $seenPages, true)) {
                throw new DuplicatedExpressCheckoutPageException();
            }

            $seenPages[] = $page;
        }
    }

    /**
     * Treats an empty style as unset and asserts that anything else is a JSON
     * object within the size limit. A decoded scalar or list is rejected: the
     * button page reads named properties off an object.
     *
     * @param string|null $buttonStyle
     *
     * @return string|null
     *
     * @throws InvalidExpressCheckoutButtonStyleException
     */
    private function normalizeButtonStyle(?string $buttonStyle): ?string
    {
        if ($buttonStyle === null || $buttonStyle === '') {
            return null;
        }

        if (\strlen($buttonStyle) > self::BUTTON_STYLE_MAX_BYTES || !\is_object(json_decode($buttonStyle))) {
            throw new InvalidExpressCheckoutButtonStyleException();
        }

        return $buttonStyle;
    }

    /**
     * @param string|null $termsAndConditionsUrl
     *
     * @return string|null
     *
     * @throws InvalidTermsAndConditionsUrlException
     */
    private function normalizeTermsAndConditionsUrl(?string $termsAndConditionsUrl): ?string
    {
        $url = trim((string) $termsAndConditionsUrl);
        if ($url === '') {
            return null;
        }

        $label = '[\p{L}\p{N}](?:[\p{L}\p{N}-]*[\p{L}\p{N}])?';
        $pattern = '#^https?://(?:[^/?\#@\s]*@)?(?:\[([0-9a-f:.]+)\]|' . $label . '(?:\.' . $label . ')*)'
            . '(?::(\d{0,5}))?(?:[/?\#]|$)#iu';
        if (
            preg_match('/[\s\x00-\x1F\x7F]/u', $url) !== 0
            || preg_match($pattern, $url, $matches) !== 1
            || (int) ($matches[2] ?? 0) > 65535
            || (($matches[1] ?? '') !== '' && filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
        ) {
            throw new InvalidTermsAndConditionsUrlException();
        }

        return $url;
    }
}
