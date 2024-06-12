<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PaymentMethod;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class SeQuraPaymentMethodTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\PaymentMethod
 */
class SeQuraPaymentMethodTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $seQuraPaymentMethod = new SeQuraPaymentMethod(
            'test',
            'test',
            'test',
            new SeQuraCost(1, 1, 1, 1),
            new DateTime('2015-09-31'),
            new DateTime('2015-09-31'),
            'test',
            'test',
            'test',
            'test',
            'test',
            1,
            1
        );

        $newCost = new SeQuraCost(2, 2, 2, 2);
        $newStartsAt = new DateTime();
        $newEndsAt = new DateTime();
        $seQuraPaymentMethod->setProduct('product');
        $seQuraPaymentMethod->setTitle('title');
        $seQuraPaymentMethod->setLongTitle('long title');
        $seQuraPaymentMethod->setCost($newCost);
        $seQuraPaymentMethod->setStartsAt($newStartsAt);
        $seQuraPaymentMethod->setEndsAt($newEndsAt);
        $seQuraPaymentMethod->setCampaign('campaign');
        $seQuraPaymentMethod->setClaim('claim');
        $seQuraPaymentMethod->setDescription('description');
        $seQuraPaymentMethod->setIcon('icon');
        $seQuraPaymentMethod->setCostDescription('cost description');
        $seQuraPaymentMethod->setMinAmount(2);
        $seQuraPaymentMethod->setMaxAmount(2);

        self::assertEquals('product', $seQuraPaymentMethod->getProduct());
        self::assertEquals('title', $seQuraPaymentMethod->getTitle());
        self::assertEquals('long title', $seQuraPaymentMethod->getLongTitle());
        self::assertEquals($newCost, $seQuraPaymentMethod->getCost());
        self::assertEquals($newStartsAt, $seQuraPaymentMethod->getStartsAt());
        self::assertEquals($newEndsAt, $seQuraPaymentMethod->getEndsAt());
        self::assertEquals('campaign', $seQuraPaymentMethod->getCampaign());
        self::assertEquals('claim', $seQuraPaymentMethod->getClaim());
        self::assertEquals('description', $seQuraPaymentMethod->getDescription());
        self::assertEquals('icon', $seQuraPaymentMethod->getIcon());
        self::assertEquals('cost description', $seQuraPaymentMethod->getCostDescription());
        self::assertEquals(2, $seQuraPaymentMethod->getMinAmount());
        self::assertEquals(2, $seQuraPaymentMethod->getMaxAmount());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFromArrayMethod(): void
    {
        $rawSeQuraPaymentMethod = [
            'product' => 'testProduct',
            'title' => 'testTitle',
            'long_title' => 'testLongTitle',
            'cost' => [
                'setup_fee' => 1,
                'instalment_fee' => 2,
                'down_payment_fees' => 3,
                'instalment_total' => 4
            ],
            'starts_at' => '2015-09-31',
            'ends_at' => '2015-09-31',
            'campaign' => 'testCampaign',
            'claim' => 'testClaim',
            'description' => 'testDescription',
            'icon' => 'testIcon',
            'cost_description' => 'testCostDescription',
            'min_amount' => 5,
            'max_amount' => 6
        ];

        $seQuraPaymentMethod = SeQuraPaymentMethod::fromArray($rawSeQuraPaymentMethod);

        self::assertEquals('testProduct', $seQuraPaymentMethod->getProduct());
        self::assertEquals('testTitle', $seQuraPaymentMethod->getTitle());
        self::assertEquals('testLongTitle', $seQuraPaymentMethod->getLongTitle());
        self::assertEquals(1, $seQuraPaymentMethod->getCost()->getSetupFee());
        self::assertEquals(2, $seQuraPaymentMethod->getCost()->getInstalmentFee());
        self::assertEquals(3, $seQuraPaymentMethod->getCost()->getDownPaymentFees());
        self::assertEquals(4, $seQuraPaymentMethod->getCost()->getInstalmentTotal());
        self::assertEquals(new DateTime('2015-09-31'), $seQuraPaymentMethod->getStartsAt());
        self::assertEquals(new DateTime('2015-09-31'), $seQuraPaymentMethod->getEndsAt());
        self::assertEquals('testCampaign', $seQuraPaymentMethod->getCampaign());
        self::assertEquals('testClaim', $seQuraPaymentMethod->getClaim());
        self::assertEquals('testDescription', $seQuraPaymentMethod->getDescription());
        self::assertEquals('testIcon', $seQuraPaymentMethod->getIcon());
        self::assertEquals('testCostDescription', $seQuraPaymentMethod->getCostDescription());
        self::assertEquals(5, $seQuraPaymentMethod->getMinAmount());
        self::assertEquals(6, $seQuraPaymentMethod->getMaxAmount());
    }
}
