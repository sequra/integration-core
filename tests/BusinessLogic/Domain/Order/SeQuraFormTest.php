<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class SeQuraFormTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Order
 */
class SeQuraFormTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $seQuraForm = new SeQuraForm('test');
        $seQuraForm->setForm('test2');

        self::assertEquals('test2', $seQuraForm->getForm());
    }
}
