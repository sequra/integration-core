<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;
use Throwable;

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
     * @var Throwable|null
     */
    private $exception;

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
     * Makes the next send throw, simulating a destination that rejects the postback.
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }

    /**
     * @inheritDoc
     */
    public function sendConversion(AffiliateConversion $conversion): bool
    {
        $this->conversions[] = $conversion;

        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->return;
    }

    /**
     * @inheritDoc
     */
    public function sendCancellation(AffiliateCancellation $cancellation): bool
    {
        $this->cancellations[] = $cancellation;

        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->return;
    }
}
