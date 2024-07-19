<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI;

use SeQura\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use SeQura\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller\SolicitationController;

/**
 * Class CheckoutAPI
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI
 */
class CheckoutAPI
{
    protected function __construct()
    {
    }

    /**
     * Gets an CheckoutAPI instance.
     *
     * @return CheckoutAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new CheckoutAPI());
    }

    /**
     * @param string $storeId
     *
     * @return SolicitationController
     */
    public function solicitation(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(SolicitationController::class);
    }
}
