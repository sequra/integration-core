<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ExpressCheckout;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
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
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     */
    public function __construct(array $expressCheckoutConfigs)
    {
        $this->expressCheckoutConfigs = $expressCheckoutConfigs;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutPageException
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

        return new self($configs);
    }

    /**
     * @return ExpressCheckoutSettings
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function transformToDomainModel(): ExpressCheckoutSettings
    {
        return new ExpressCheckoutSettings($this->expressCheckoutConfigs);
    }
}
