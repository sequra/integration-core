<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests\CancellationHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate\Requests\ConversionHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AffiliateProxyFactory;

/**
 * Class AffiliateProxy.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate
 */
class AffiliateProxy implements AffiliateProxyInterface
{
    /**
     * @var AffiliateProxyFactory $affiliateProxyFactory
     */
    private $affiliateProxyFactory;

    /**
     * @param AffiliateProxyFactory $affiliateProxyFactory
     */
    public function __construct(AffiliateProxyFactory $affiliateProxyFactory)
    {
        $this->affiliateProxyFactory = $affiliateProxyFactory;
    }

    /**
     * @inheritDoc
     */
    public function sendConversion(AffiliateConversion $conversion): bool
    {
        return $this->affiliateProxyFactory->build($conversion->getMerchantId())
            ->get(new ConversionHttpRequest($conversion))
            ->isSuccessful();
    }

    /**
     * @inheritDoc
     */
    public function sendCancellation(AffiliateCancellation $cancellation): bool
    {
        return $this->affiliateProxyFactory->build($cancellation->getMerchantId())
            ->post(new CancellationHttpRequest($cancellation))
            ->isSuccessful();
    }
}
