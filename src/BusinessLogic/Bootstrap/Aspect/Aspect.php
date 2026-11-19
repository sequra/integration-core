<?php

namespace SeQura\Core\BusinessLogic\Bootstrap\Aspect;

/**
 * Interface Aspect
 *
 * @package SeQura\Core\BusinessLogic\Bootstrap\Aspect
 */
interface Aspect
{
    /**
     * @param callable $callee
     * @param mixed[] $params
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function applyOn(callable $callee, array $params = []);
}
