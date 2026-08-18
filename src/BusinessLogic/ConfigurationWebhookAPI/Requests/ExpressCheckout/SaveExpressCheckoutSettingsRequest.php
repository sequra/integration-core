<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutButtonStyleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
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
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     * @param string|null $buttonStyle
     */
    public function __construct(array $expressCheckoutConfigs, ?string $buttonStyle = null)
    {
        $this->expressCheckoutConfigs = $expressCheckoutConfigs;
        $this->buttonStyle = $buttonStyle;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutPageException
     * @throws InvalidExpressCheckoutButtonStyleException
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

        if ($buttonStyle !== null && !self::isWellFormedJson($buttonStyle)) {
            throw new InvalidExpressCheckoutButtonStyleException();
        }

        return new self($configs, $buttonStyle);
    }

    /**
     * @return ExpressCheckoutSettings
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function transformToDomainModel(): ExpressCheckoutSettings
    {
        return new ExpressCheckoutSettings($this->expressCheckoutConfigs, $this->buttonStyle);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private static function isWellFormedJson($value): bool
    {
        if (!\is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
