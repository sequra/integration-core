<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Aspects;

use Exception;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use SeQura\Core\Infrastructure\Exceptions\BaseException;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

/**
 * Class ErrorHandlingAspect
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Aspects
 */
class ErrorHandlingAspect implements Aspect
{
    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        try {
            $response = call_user_func_array($callee, $params);
        } catch (BaseException $e) {
            Logger::logError(
                $e->getMessage(),
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $response = $e->getMessage();
        } catch (Throwable $e) {
            Logger::logError(
                'Unhandled error occurred.',
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $response = $e;
        }

        return $response;
    }
}
