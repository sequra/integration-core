<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutButtonStyleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidTermsAndConditionsUrlException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;

/**
 * Class SaveExpressCheckoutSettingsRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout
 */
class SaveExpressCheckoutSettingsRequest extends ConfigurationWebhookRequest
{
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
     * @var bool
     */
    protected $hasTermsAndConditionsUrl;

    /**
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     * @param string|null $buttonStyle
     * @param string|null $termsAndConditionsUrl
     * @param bool $hasTermsAndConditionsUrl
     */
    public function __construct(
        array $expressCheckoutConfigs,
        ?string $buttonStyle = null,
        ?string $termsAndConditionsUrl = null,
        bool $hasTermsAndConditionsUrl = false
    ) {
        $this->expressCheckoutConfigs = $expressCheckoutConfigs;
        $this->buttonStyle = $buttonStyle;
        $this->termsAndConditionsUrl = $termsAndConditionsUrl;
        $this->hasTermsAndConditionsUrl = $hasTermsAndConditionsUrl;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws InvalidExpressCheckoutButtonStyleException
     * @throws InvalidTermsAndConditionsUrlException
     */
    public static function fromPayload(array $payload): object
    {
        $rawConfigs = $payload['expressCheckoutConfigs'] ?? [];
        $configs = [];

        foreach ($rawConfigs as $configData) {
            if (\is_array($configData)) {
                $configs[] = ExpressCheckoutPageConfig::fromArray($configData);
            }
        }

        $buttonStyle = $payload['buttonStyle'] ?? null;

        if ($buttonStyle !== null && !\is_string($buttonStyle)) {
            throw new InvalidExpressCheckoutButtonStyleException();
        }

        $termsAndConditionsUrl = $payload['termsAndConditionsUrl'] ?? null;

        if ($termsAndConditionsUrl !== null && !\is_string($termsAndConditionsUrl)) {
            throw new InvalidTermsAndConditionsUrlException();
        }

        return new self(
            $configs,
            $buttonStyle,
            $termsAndConditionsUrl,
            \array_key_exists('termsAndConditionsUrl', $payload)
        );
    }

    /**
     * @return bool
     */
    public function hasTermsAndConditionsUrl(): bool
    {
        return $this->hasTermsAndConditionsUrl;
    }

    /**
     * @param string|null $storedTermsAndConditionsUrl
     *
     * @return ExpressCheckoutSettings
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws InvalidExpressCheckoutButtonStyleException
     * @throws InvalidTermsAndConditionsUrlException
     */
    public function transformToDomainModel(?string $storedTermsAndConditionsUrl = null): ExpressCheckoutSettings
    {
        return new ExpressCheckoutSettings(
            $this->expressCheckoutConfigs,
            $this->buttonStyle,
            $this->hasTermsAndConditionsUrl ? $this->termsAndConditionsUrl : $storedTermsAndConditionsUrl
        );
    }
}
