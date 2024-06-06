<?php

namespace SeQura\Core\BusinessLogic\Bootstrap\Aspect;

/**
 * Interface Aspect
 *
 * @package SeQura\Core\BusinessLogic\Bootstrap\Aspect
 */
interface Aspect
{
    public function applyOn(callable $callee, array $params = []);
}
