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
     * Returns a proxy of the given instance: every method call on it is the call on
     * the instance with the aspects applied around it, so a caller works with the
     * type it passed in.
     *
     * @param T $subject
     *
     * @template T of object
     *
     * @return T
     */
    public function beforeEachMethodOfInstance(object $subject): object
    {
        $this->subject = $subject;
        $this->subjectClassName = null;

        // Every call on this instance is forwarded to the subject, so it stands in for it.
        /**
 * @var T $proxy
*/
        $proxy = $this;

        return $proxy;
    }

    /**
     * Returns a proxy of the given service: every method call on it is the call on
     * the registered service with the aspects applied around it, so a caller works
     * with the type it asked for.
     *
     * @param class-string<T> $serviceClass
     *
     * @template T of object
     *
     * @return T
     */
    public function beforeEachMethodOfService(string $serviceClass): object
    {
        $this->subjectClassName = $serviceClass;
        $this->subject = null;

        // Every call on this instance is forwarded to the service, so it stands in for it.
        /**
 * @var T $proxy
*/
        $proxy = $this;

        return $proxy;
    }

    /**
     * @param string $methodName
     * @param mixed[] $arguments
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call(string $methodName, array $arguments)
    {
        if ($this->subject) {
            return $this->aspect->applyOn([$this->subject, $methodName], $arguments);
        }

        return $this->aspect->applyOn(function () use ($methodName, $arguments) {
            $subject = ServiceRegister::getService($this->subjectClassName);

            return \call_user_func_array([$subject, $methodName], $arguments);
        });
    }
}
