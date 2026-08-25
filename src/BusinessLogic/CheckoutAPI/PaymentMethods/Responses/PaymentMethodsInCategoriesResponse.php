<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;

/**
 * Class PaymentMethodsInCategoriesResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses
 */
class PaymentMethodsInCategoriesResponse extends Response
{
    /**
     * @var SeQuraPaymentMethodCategory[]
     */
    protected $paymentMethodCategories;

    /**
     * @param SeQuraPaymentMethodCategory[] $paymentMethodCategories
     */
    public function __construct(array $paymentMethodCategories)
    {
        $this->paymentMethodCategories = $paymentMethodCategories;
    }

    /**
     * @return SeQuraPaymentMethodCategory[]
     */
    public function getPaymentMethodCategories(): array
    {
        return $this->paymentMethodCategories;
    }

    /**
     * Determines whether at least one category offers a payment method. Categories with no method are returned
     * as well, so the presence of categories does not mean the buyer has anything to choose from.
     *
     * Only meaningful once isSuccessful() has passed: a failed call answers with an ErrorResponse, whose
     * __call returns the response itself for every unknown method, so this reads as truthy rather than false.
     *
     * @return bool
     */
    public function hasAvailablePaymentMethods(): bool
    {
        foreach ($this->paymentMethodCategories as $category) {
            if (!empty($category->getMethods())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $categories = [];
        foreach ($this->paymentMethodCategories as $category) {
            $categories[] = [
                'title' => $category->getTitle(),
                'description' => $category->getDescription(),
                'icon' => $category->getIcon(),
                'methods' => array_map([$this, 'paymentMethodToArray'], $category->getMethods()),
            ];
        }

        return $categories;
    }

    /**
     * Serializes a payment method for the storefront. The fields are named as CachedPaymentMethodsResponse
     * names them, so a storefront reads one shape whichever checkout endpoint it calls, and written out here
     * rather than delegated to the model, whose own toArray() is the format the payment method is stored in.
     *
     * @param SeQuraPaymentMethod $paymentMethod
     *
     * @return mixed[]
     */
    protected function paymentMethodToArray(SeQuraPaymentMethod $paymentMethod): array
    {
        return [
            'product' => $paymentMethod->getProduct(),
            'title' => $paymentMethod->getTitle(),
            'longTitle' => $paymentMethod->getLongTitle(),
            'cost' => [
                'setupFee' => $paymentMethod->getCost()->getSetupFee(),
                'instalmentFee' => $paymentMethod->getCost()->getInstalmentFee(),
                'downPaymentFees' => $paymentMethod->getCost()->getDownPaymentFees(),
                'instalmentTotal' => $paymentMethod->getCost()->getInstalmentTotal(),
            ],
            'startsAt' => $paymentMethod->getStartsAt()->format('Y-m-d H:i:s'),
            'endsAt' => $paymentMethod->getEndsAt()->format('Y-m-d H:i:s'),
            'campaign' => $paymentMethod->getCampaign(),
            'claim' => $paymentMethod->getClaim(),
            'description' => $paymentMethod->getDescription(),
            'icon' => $paymentMethod->getIcon(),
            'costDescription' => $paymentMethod->getCostDescription(),
            'minAmount' => $paymentMethod->getMinAmount(),
            'maxAmount' => $paymentMethod->getMaxAmount(),
        ];
    }
}
