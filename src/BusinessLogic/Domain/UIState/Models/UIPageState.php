<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Models;

use SeQura\Core\BusinessLogic\Domain\UIState\Exception\InvalidUIPageException;
use SeQura\Core\BusinessLogic\Domain\UIState\Exception\InvalidUIStateException;
use SeQura\Core\BusinessLogic\Domain\UIState\PageStates;

/**
 * Class UIState
 *
 * @package SeQura\Core\BusinessLogic\Domain\UIState\Models
 */
class UIPageState
{
    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $page;

    /**
     * @param string $state
     * @param string|null $page
     *
     * @throws InvalidUIStateException
     * @throws InvalidUIPageException
     */
    public function __construct(string $state, ?string $page)
    {
        if(!in_array($state, [PageStates::STATE_DASHBOARD, PageStates::STATE_ONBOARDING], true)) {
            throw new InvalidUIStateException('Invalid UI state.');
        }

        if(
            ($page && $state === PageStates::STATE_DASHBOARD && !in_array($page, PageStates::DASHBOARD_PAGES, true)) ||
            ($page && $state === PageStates::STATE_ONBOARDING && !in_array($page, PageStates::ONBOARDING_PAGES, true))
        ) {
            throw new InvalidUIPageException('Invalid UI page.');
        }

        $this->state = $state;
        $this->page = $page;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * @param string $page
     */
    public function setPage(string $page): void
    {
        $this->page = $page;
    }
}
