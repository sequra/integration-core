<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;

/**
 * Class MockAffiliateProxy.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockAffiliateProxy implements AffiliateProxyInterface
{
    /**
     * @var AffiliateConversion[]
     */
    public $conversions = [];

    /**
     * @var AffiliateCancellation[]
     */
    public $cancellations = [];

    /**
     * @var bool
     */
    private $return = true;

    /**
     * @param bool $return
     *
     * @return void
     */
    public function setReturn(bool $return): void
    {
        $this->return = $return;
    }

    /**
     * @inheritDoc
     */
    public function sendConversion(AffiliateConversion $conversion): bool
    {
        $this->conversions[] = $conversion;

        return $this->return;
    }

    /**
     * @inheritDoc
     */
    public function sendCancellation(AffiliateCancellation $cancellation): bool
    {
        $this->cancellations[] = $cancellation;

        return $this->return;
    }
}
