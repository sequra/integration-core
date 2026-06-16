<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateSettingsService;

/**
 * Class MockAffiliateSettingsService.
 *
 * @package Common\MockComponents
 */
class MockAffiliateSettingsService extends AffiliateSettingsService
{
    /**
     * @var ?AffiliateSettings $affiliateSettings
     */
    private $affiliateSettings;

    /**
     * @return ?AffiliateSettings
     */
    public function getAffiliateSettings(): ?AffiliateSettings
    {
        return $this->affiliateSettings;
    }

    /**
     * @param ?AffiliateSettings $affiliateSettings
     *
     * @return void
     */
    public function setAffiliateSettings(?AffiliateSettings $affiliateSettings): void
    {
        $this->affiliateSettings = $affiliateSettings;
    }
}
