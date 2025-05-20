<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class FormattedPaymentMethodsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses
 */
class FormattedPaymentMethodsResponse extends Response
{
    /**
     * @var SeQuraPaymentMethod[]
     */
    protected $paymentMethods;

    /**
     * @param SeQuraPaymentMethod[] $paymentMethods
     */
    public function __construct(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $methods = [];
        foreach ($this->paymentMethods as $paymentMethod) {
            $category = $paymentMethod->getCategory();
            if (!in_array($category, WidgetSettingsService::WIDGET_SUPPORTED_CATEGORIES)) {
                continue;
            }

            $product = $paymentMethod->getProduct();
            $title = $paymentMethod->getTitle();

            if (!isset($methods[$category])) {
                $methods[$category] = [];
            }

            if (!isset($methods[$category][$product])) {
                $methods[$category][$product] = [
                    'category' => $category,
                    'product' => $product,
                    'title' => $title,
                ];

                continue;
            }

            $methods[$category][$product]['title'] .= '/' . $title;
        }

        return array_map(static function ($categoryMethods) {
            return array_values($categoryMethods);
        }, $methods);
    }
}
