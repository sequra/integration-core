<?php

namespace SeQura\Core\Tests\Infrastructure\ORM;

use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;

/**
 * Class MemoryGenericStudentRepositoryTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\ORM
 */
class MemoryGenericStudentRepositoryTest extends AbstractGenericStudentRepositoryTest
{
    /**
     * @return string
     */
    public function getStudentEntityRepositoryClass()
    {
        return MemoryRepository::getClassName();
    }

    /**
     * Cleans up all storage Services used by repositories
     */
    public function cleanUpStorage()
    {
        MemoryStorage::reset();
    }
}
