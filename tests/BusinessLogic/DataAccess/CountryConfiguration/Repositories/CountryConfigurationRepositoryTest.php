<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\CountryConfiguration\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration as CountryConfigurationEntity;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CountryConfigurationRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\CountryConfiguration\Repositories
 */
class CountryConfigurationRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var CountryConfigurationRepositoryInterface */
    private $countryConfigurationRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(CountryConfigurationEntity::getClassName());
        $this->countryConfigurationRepository = new CountryConfigurationRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(CountryConfigurationRepositoryInterface::class, function () {
            return $this->countryConfigurationRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteCountryConfiguration(): void
    {
        // arrange
        $countryConfigurations = [
            new CountryConfiguration(
                'ES',
                'spain'
            ),
            new CountryConfiguration(
                'FR',
                'france'
            )
        ];

        $entity = new CountryConfigurationEntity();
        $entity->setStoreId('1');
        $entity->setCountryConfiguration($countryConfigurations);
        $this->repository->save($entity);


        // act
        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'deleteCountryConfigurations']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
