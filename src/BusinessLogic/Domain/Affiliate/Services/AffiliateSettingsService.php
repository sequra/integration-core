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
     * Returns the stored affiliate settings, or a safe disabled default when none are stored, so
     * consumers never have to null-check and "absent" deterministically means disabled.
     *
     * @return AffiliateSettings
     */
    public function getAffiliateSettings(): AffiliateSettings
    {
        return $this->affiliateSettingsRepository->getAffiliateSettings() ?? new AffiliateSettings(false, '', '');
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
