<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;

/**
 * Class ExpressCheckoutSettings
 *
 * Aggregate of per-page Express Checkout configurations for a single store.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutSettings
{
    /**
     * @var ExpressCheckoutPageConfig[]
     */
    protected $expressCheckoutConfigs;

    /**
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     *
     * @throws InvalidExpressCheckoutPageConfigException When an entry is not an ExpressCheckoutPageConfig.
     * @throws DuplicatedExpressCheckoutPageException When two entries reference the same page.
     */
    public function __construct(array $expressCheckoutConfigs = [])
    {
        $this->validateConfigs($expressCheckoutConfigs);
        $this->expressCheckoutConfigs = array_values($expressCheckoutConfigs);
    }

    /**
     * @return ExpressCheckoutPageConfig[]
     */
    public function getExpressCheckoutConfigs(): array
    {
        return $this->expressCheckoutConfigs;
    }

    /**
     * @param string $page Page identifier string (see ExpressCheckoutPage factories).
     *
     * @return bool
     */
    public function isPageEnabled(string $page): bool
    {
        foreach ($this->expressCheckoutConfigs as $config) {
            if ($config->getPage()->getPage() === $page) {
                return $config->isEnabled();
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expressCheckoutConfigs' => array_map(static function (ExpressCheckoutPageConfig $config) {
                return $config->toArray();
            }, $this->expressCheckoutConfigs),
        ];
    }

    /**
     * Asserts that each entry is an ExpressCheckoutPageConfig and that no two
     * entries reference the same page.
     *
     * @param ExpressCheckoutPageConfig[] $expressCheckoutConfigs
     *
     * @return void
     *
     * @throws InvalidExpressCheckoutPageConfigException
     * @throws DuplicatedExpressCheckoutPageException
     */
    private function validateConfigs(array $expressCheckoutConfigs): void
    {
        $seenPages = [];

        foreach ($expressCheckoutConfigs as $config) {
            if (!$config instanceof ExpressCheckoutPageConfig) {
                throw new InvalidExpressCheckoutPageConfigException();
            }

            $page = $config->getPage()->getPage();
            if (\in_array($page, $seenPages, true)) {
                throw new DuplicatedExpressCheckoutPageException();
            }

            $seenPages[] = $page;
        }
    }
}
