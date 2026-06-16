<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\Services;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts\AffiliateSettingsRepositoryInterface;

/**
 * Class AffiliateSettingsService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\Services
 */
class AffiliateSettingsService
{
    /**
     * @var AffiliateSettingsRepositoryInterface $affiliateSettingsRepository
     */
    private $affiliateSettingsRepository;

    /**
     * @param AffiliateSettingsRepositoryInterface $affiliateSettingsRepository
     */
    public function __construct(AffiliateSettingsRepositoryInterface $affiliateSettingsRepository)
    {
        $this->affiliateSettingsRepository = $affiliateSettingsRepository;
    }

    /**
     * @return ?AffiliateSettings
     */
    public function getAffiliateSettings(): ?AffiliateSettings
    {
        return $this->affiliateSettingsRepository->getAffiliateSettings();
    }

    /**
     * @param AffiliateSettings $affiliateSettings
     *
     * @return void
     */
    public function setAffiliateSettings(AffiliateSettings $affiliateSettings): void
    {
        $this->affiliateSettingsRepository->setAffiliateSettings($affiliateSettings);
    }
}
