<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PaymentMethod;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class SeQuraCostTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\PaymentMethod
 */
class SeQuraCostTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $seQuraCost = new SeQuraCost(1, 1, 1, 1);

        $seQuraCost->setSetupFee(2);
        $seQuraCost->setInstalmentFee(2);
        $seQuraCost->setDownPaymentFees(2);
        $seQuraCost->setInstalmentTotal(2);

        self::assertEquals(2, $seQuraCost->getSetupFee());
        self::assertEquals(2, $seQuraCost->getInstalmentFee());
        self::assertEquals(2, $seQuraCost->getDownPaymentFees());
        self::assertEquals(2, $seQuraCost->getInstalmentTotal());
    }
}
