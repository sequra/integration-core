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
                'methods' => array_map(static function (SeQuraPaymentMethod $paymentMethod) {
                    return $paymentMethod->toArray();
                }, $category->getMethods()),
            ];
        }

        return $categories;
    }
}
