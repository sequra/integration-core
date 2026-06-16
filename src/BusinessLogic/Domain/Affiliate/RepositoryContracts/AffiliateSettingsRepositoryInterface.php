<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;

/**
 * Interface AffiliateSettingsRepositoryInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts
 */
interface AffiliateSettingsRepositoryInterface
{
    /**
     * @return ?AffiliateSettings
     */
    public function getAffiliateSettings(): ?AffiliateSettings;

    /**
     * @param AffiliateSettings $settings
     *
     * @return void
     */
    public function setAffiliateSettings(AffiliateSettings $settings): void;

    /**
     * @return void
     */
    public function deleteAffiliateSettings(): void;
}
