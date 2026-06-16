<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts\AffiliateSettingsRepositoryInterface;

/**
 * Class MockAffiliateSettingsRepository.
 *
 * @package Common\MockComponents
 */
class MockAffiliateSettingsRepository implements AffiliateSettingsRepositoryInterface
{
    /**
     * @var ?AffiliateSettings $settings
     */
    private $settings;

    /**
     * @inheritDoc
     */
    public function getAffiliateSettings(): ?AffiliateSettings
    {
        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function setAffiliateSettings(AffiliateSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function deleteAffiliateSettings(): void
    {
        $this->settings = null;
    }
}
