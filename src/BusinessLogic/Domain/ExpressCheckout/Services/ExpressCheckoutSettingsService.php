<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;

/**
 * Class ExpressCheckoutSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutSettingsService
{
    /**
     * @var ExpressCheckoutSettingsRepositoryInterface
     */
    protected $expressCheckoutSettingsRepository;

    /**
     * @param ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
     */
    public function __construct(ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository)
    {
        $this->expressCheckoutSettingsRepository = $expressCheckoutSettingsRepository;
    }

    /**
     * Retrieves Express Checkout settings from the repository.
     *
     * @return ExpressCheckoutSettings|null
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        return $this->expressCheckoutSettingsRepository->getExpressCheckoutSettings();
    }

    /**
     * Persists Express Checkout settings via the repository.
     *
     * @param ExpressCheckoutSettings $settings
     *
     * @return void
     */
    public function saveExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $this->expressCheckoutSettingsRepository->setExpressCheckoutSettings($settings);
    }
}
