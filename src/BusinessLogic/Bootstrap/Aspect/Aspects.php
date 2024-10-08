<?php

namespace SeQura\Core\BusinessLogic\Bootstrap\Aspect;

use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class Aspects
 *
 * @phpstan-consistent-constructor
 *
 * @package SeQura\Core\BusinessLogic\Bootstrap\Aspect
 */
class Aspects
{
    /**
     * @var object|null
     */
    protected $subject;
    /**
     * @var class-string|null
     */
    protected $subjectClassName;
    /**
     * @var Aspect
     */
    protected $aspect;

    /**
     * Aspects constructor.
     *
     * @param Aspect $aspect
     */
    protected function __construct(Aspect $aspect)
    {
        $this->aspect = $aspect;
    }

    public static function run(Aspect $aspect): Aspects
    {
        return new static($aspect);
    }

    public function andRun(Aspect $aspect): Aspects
    {
        $this->aspect = new CompositeAspect($this->aspect);
        $this->aspect->append($aspect);

        return $this;
    }

    /**
     * @param object $subject
     *
     * @return Aspects
     */
    public function beforeEachMethodOfInstance($subject): Aspects
    {
        $this->subject = $subject;
        $this->subjectClassName = null;
        return $this;
    }

    /**
     * @param class-string $serviceClass
     *
     * @return Aspects
     */
    public function beforeEachMethodOfService(string $serviceClass): Aspects
    {
        $this->subjectClassName = $serviceClass;
        $this->subject = null;
        return $this;
    }

    /**
     * @param string $methodName
     * @param mixed[] $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call($methodName, $arguments)
    {
        if ($this->subject) {
            return $this->aspect->applyOn([$this->subject, $methodName], $arguments);
        }

        return $this->aspect->applyOn(function () use ($methodName, $arguments) {
            $subject = ServiceRegister::getService($this->subjectClassName);

            return call_user_func_array([$subject, $methodName], $arguments);
        });
    }
}
