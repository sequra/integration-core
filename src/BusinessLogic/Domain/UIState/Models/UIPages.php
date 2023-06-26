<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Models;

use SeQura\Core\BusinessLogic\Domain\UIState\Exception\InvalidUIPageException;
use SeQura\Core\BusinessLogic\Domain\UIState\PageStates;

/**
 * Class UIPages
 *
 * @package SeQura\Core\BusinessLogic\Domain\UIState\Models
 */
class UIPages
{
    /**
     * @var string[]
     */
    private $dashboardPages;

    /**
     * @var string[]
     */
    private $onboardingPages;

    /**
     * @param string[] $dashboardPages
     * @param string[] $onboardingPages
     *
     * @throws InvalidUIPageException
     */
    public function __construct(array $dashboardPages, array $onboardingPages)
    {
        foreach ($dashboardPages as $dashboardPage) {
            if (!in_array($dashboardPage, PageStates::DASHBOARD_PAGES)) {
                throw new InvalidUIPageException('Invalid dashboard page.');
            }
        }

        foreach ($onboardingPages as $onboardingPage) {
            if (!in_array($onboardingPage, PageStates::ONBOARDING_PAGES)) {
                throw new InvalidUIPageException('Invalid onboarding page.');
            }
        }

        $this->dashboardPages = $dashboardPages;
        $this->onboardingPages = $onboardingPages;
    }

    /**
     * @return string[]
     */
    public function getDashboardPages(): array
    {
        return $this->dashboardPages;
    }

    /**
     * @param string[] $dashboardPages
     */
    public function setDashboardPages(array $dashboardPages): void
    {
        $this->dashboardPages = $dashboardPages;
    }

    /**
     * @return string[]
     */
    public function getOnboardingPages(): array
    {
        return $this->onboardingPages;
    }

    /**
     * @param string[] $onboardingPages
     */
    public function setOnboardingPages(array $onboardingPages): void
    {
        $this->onboardingPages = $onboardingPages;
    }
}
