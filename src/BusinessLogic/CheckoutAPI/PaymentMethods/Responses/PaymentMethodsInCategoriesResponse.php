<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;

/**
 * Class PaymentMethodsInCategoriesResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses
 */
class PaymentMethodsInCategoriesResponse extends Response
{
    use PaymentMethodResponseTrait;

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

        return [
            'categories' => $categories,
            'hasAvailablePaymentMethods' => $this->hasAvailablePaymentMethods(),
        ];
    }

    /**
     * Categories with no method are returned as well, so the presence of categories does not mean the buyer
     * has anything to choose from. Answered as part of the payload rather than as a method of its own: a
     * failed call is an ErrorResponse, whose __call returns the response itself for every unknown method,
     * so a caller asking a response object would read a failure as truthy.
     *
     * @return bool
     */
    private function hasAvailablePaymentMethods(): bool
    {
        foreach ($this->paymentMethodCategories as $category) {
            if (!empty($category->getMethods())) {
                return true;
            }
        }

        return false;
    }
}
