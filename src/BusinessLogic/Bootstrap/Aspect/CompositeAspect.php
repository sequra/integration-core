<?php

namespace SeQura\Core\BusinessLogic\Bootstrap\Aspect;

/**
 * Class CompositeAspect
 *
 * @package SeQura\Core\BusinessLogic\Bootstrap\Aspect
 */
class CompositeAspect
{
    /**
     * @var Aspect
     */
    protected $aspect;
    /**
     * @var Aspect|null
     */
    protected $next;

    public function __construct(Aspect $aspect)
    {
        $this->aspect = $aspect;
    }

    public function append(Aspect $aspect): void
    {
        $this->next = new self($aspect);
    }

    /**
     * @throws \Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        $callback = $callee;
        if ($this->next) {
            $callback = $this->getNextCallee($callee, $params);
        }

        return $this->aspect->applyOn($callback, $params);
    }

    protected function getNextCallee(callable $callee, array $params = []): \Closure
    {
        return function () use ($callee, $params) {
            return $this->next->applyOn($callee, $params);
        };
    }
}
