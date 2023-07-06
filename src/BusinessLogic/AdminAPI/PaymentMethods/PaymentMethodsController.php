<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods;

use DateTime;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\PaymentMethodsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Class PaymentMethodsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods
 */
class PaymentMethodsController
{
    /**
     * Gets all the available payment methods for the merchant.
     *
     * @return PaymentMethodsResponse
     */
    public function getPaymentMethods(): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse([
            new SeQuraPaymentMethod(
                'i1',
                'title1',
                'longTitle1',
                new SeQuraCost(1,2,3,4),
                new DateTime(),
                new DateTime(),
                'campaign1',
                'claim1',
                'description1',
                'icon1',
                'costDescription1',
                1234567.89,
                321
            ),
            new SeQuraPaymentMethod(
                'pp3',
                'title2',
                'longTitle2',
                new SeQuraCost(5,6,7,8),
                new DateTime(),
                new DateTime(),
                'campaign2',
                'claim2',
                'description2',
                'icon2',
                'costDescription2',
                456,
                654
            ),
            new SeQuraPaymentMethod(
                'pp5',
                'title3',
                'longTitle3',
                new SeQuraCost(5,6,7,8),
                new DateTime(),
                new DateTime(),
                'campaign3',
                'claim3',
                'description3',
                'icon3',
                'costDescription3',
                456,
                654
            )
        ]);
    }
}
