<?php

namespace SeQura\Core\BusinessLogic\Bootstrap\Aspect;

use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class Aspects
 *
 * @template T
 *
 * @package SeQura\Core\BusinessLogic\Bootstrap\Aspect
 */
/** @phpstan-consistent-constructor */
class Aspects
{
    /**
     * @var T|null
     */
    // @phpstan-ignore-next-line
    protected $subject;
    /**
     * @var class-string<T>|null
     */
    // @phpstan-ignore-next-line
    protected $subjectClassName;
    /**
     * @var Aspect
     */
    protected $aspect;

    protected function __construct(Aspect $aspect)
    {
        $this->aspect = $aspect;
    }

    public static function run(Aspect $aspect): self
    {
        return new static($aspect);
    }

    public function andRun(Aspect $aspect): self
    {
        $this->aspect = new CompositeAspect($this->aspect);
        $this->aspect->append($aspect);

        return $this;
    }

    /**
     * @param T $subject
     *
     * // @phpstan-ignore-next-line
     * @return T
     */
    public function beforeEachMethodOfInstance($subject)
    {
        $this->subject = $subject;
        $this->subjectClassName = null;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        // @phpstan-ignore-next-line
        return $this;
    }

    /**
     * @param class-string<T> $serviceClass
     *
     * // @phpstan-ignore-next-line
     * @return T
     */
    public function beforeEachMethodOfService(string $serviceClass)
    {
        $this->subjectClassName = $serviceClass;
        $this->subject = null;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        // @phpstan-ignore-next-line
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
