<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Request;

/**
 * Class Request
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Request
 */
abstract class Request
{
    /**
     * Transform to Domain model based on data sent from controller.
     *
     * @return object|object[]
     */
    abstract public function transformToDomainModel();
}
