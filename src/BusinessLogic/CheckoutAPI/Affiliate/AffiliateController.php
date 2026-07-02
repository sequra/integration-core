<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate;

use SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests\SendCancellationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests\SendConversionRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Responses\AffiliatePostbackResponse;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class AffiliateController.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate
 */
class AffiliateController
{
    /**
     * @var AffiliateService $affiliateService
     */
    private $affiliateService;

    /**
     * @param AffiliateService $affiliateService
     */
    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Reports a conversion to the affiliate network.
     *
     * @param SendConversionRequest $request
     *
     * @return AffiliatePostbackResponse
     *
     * @throws HttpRequestException
     */
    public function reportConversion(SendConversionRequest $request): AffiliatePostbackResponse
    {
        return new AffiliatePostbackResponse($this->affiliateService->reportConversion(
            $request->getMerchantId(),
            $request->getTransactionId(),
            $request->getAmount(),
            $request->getOrderReference()
        ));
    }

    /**
     * Reports a cancellation to seQura's conversion-status webhook.
     *
     * @param SendCancellationRequest $request
     *
     * @return AffiliatePostbackResponse
     *
     * @throws HttpRequestException
     */
    public function reportCancellation(SendCancellationRequest $request): AffiliatePostbackResponse
    {
        return new AffiliatePostbackResponse($this->affiliateService->reportCancellation(
            $request->getMerchantId(),
            $request->getTransactionId()
        ));
    }
}
