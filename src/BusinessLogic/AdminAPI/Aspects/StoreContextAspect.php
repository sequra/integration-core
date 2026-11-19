<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Aspects;

use Exception;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class StoreContextAspect
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Aspects
 */
class StoreContextAspect implements Aspect
{
    /**
     * @var string
     */
    protected $storeId;

    public function __construct(string $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        return StoreContext::doWithStore($this->storeId, $callee, $params);
    }
}
