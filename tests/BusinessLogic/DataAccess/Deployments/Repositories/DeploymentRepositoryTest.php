<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Deployments\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities\Deployment as DeploymentEntity;
use SeQura\Core\BusinessLogic\DataAccess\Deployments\Repositories\DeploymentRepository;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class DeploymentRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Deployments\Repositories
 */
class DeploymentRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var DeploymentsRepositoryInterface */
    private $deploymentRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(DeploymentEntity::getClassName());
        $this->deploymentRepository = new DeploymentRepository(
            TestRepositoryRegistry::getRepository(DeploymentEntity::getClassName()),
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(
            DeploymentsRepositoryInterface::class,
            function () {
                return $this->deploymentRepository;
            }
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetDeploymentsNoDeployments(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'getDeployments']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetDeployments(): void
    {
        // arrange
        $deployments = [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];

        foreach ($deployments as $deployment) {
            $entity = new DeploymentEntity();
            $entity->setDeployment($deployment);
            $entity->setStoreId('1');
            $entity->setDeploymentId($deployment->getId());

            $this->repository->save($entity);
        }

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'getDeployments']
        );

        // assert
        self::assertCount(2, $result);
        self::assertEquals($deployments, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentByIdNoDeployment(): void
    {
        // arrange

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'getDeploymentById'],
            ['sequra']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetDeploymentById(): void
    {
        // arrange
        $deployments = [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];

        foreach ($deployments as $deployment) {
            $entity = new DeploymentEntity();
            $entity->setDeployment($deployment);
            $entity->setStoreId('1');
            $entity->setDeploymentId($deployment->getId());

            $this->repository->save($entity);
        }

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'getDeploymentById'],
            ['sequra']
        );

        // assert
        $expectedSequraDeployment = new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );

        self::assertEquals($expectedSequraDeployment, $result);
    }

    /**
     * @throws Exception
     */
    public function testSetDeployments(): void
    {
        // arrange
        $sequraDeployment = new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );

        $deployments = [$sequraDeployment, $sveaDeployment];

        // act
        StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'setDeployments'],
            [$deployments]
        );

        // assert
        /** @var DeploymentEntity[] $result */
        $result = $this->repository->select();
        self::assertCount(2, $result);
        self::assertEquals($result[0]->getDeployment(), $sequraDeployment);
        self::assertEquals($result[1]->getDeployment(), $sveaDeployment);
    }

    /**
     * @throws Exception
     */
    public function testSetDeploymentsWithUpdate(): void
    {
        // arrange
        $sequraDeployment = new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea',
            'SVEA',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );

        $deployments = [$sequraDeployment, $sveaDeployment];

        foreach ($deployments as $deployment) {
            $entity = new DeploymentEntity();
            $entity->setDeployment($deployment);
            $entity->setStoreId('1');
            $entity->setDeploymentId($deployment->getId());

            $this->repository->save($entity);
        }

        $sequraDeployment = new Deployment(
            'sequra',
            'seQura New',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
        $sveaDeployment = new Deployment(
            'svea',
            'SVEA New',
            new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
            new DeploymentURL(
                'https://next-sandbox.sequra.svea.com/',
                'https://next-sandbox.cdn.sequra.svea.com/assets/'
            )
        );
        $deployments = [$sequraDeployment, $sveaDeployment];

        // act
        StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'setDeployments'],
            [$deployments]
        );

        // assert
        /** @var DeploymentEntity[] $result */
        $result = $this->repository->select();
        self::assertCount(2, $result);
        self::assertEquals($result[0]->getDeployment(), $sequraDeployment);
        self::assertEquals($result[1]->getDeployment(), $sveaDeployment);
    }

    /**
     * @throws Exception
     */
    public function testDeleteDeployments(): void
    {
        // arrange
        $deployments = [
            new Deployment(
                'sequra',
                'seQura',
                new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
                new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
            ),
            new Deployment(
                'svea',
                'SVEA',
                new DeploymentURL('https://live.sequra.svea.com/', 'https://live.cdn.sequra.svea.com/assets/'),
                new DeploymentURL(
                    'https://next-sandbox.sequra.svea.com/',
                    'https://next-sandbox.cdn.sequra.svea.com/assets/'
                )
            )
        ];

        foreach ($deployments as $deployment) {
            $entity = new DeploymentEntity();
            $entity->setDeployment($deployment);
            $entity->setStoreId('1');
            $entity->setDeploymentId($deployment->getId());

            $this->repository->save($entity);
        }

        // act
        StoreContext::doWithStore(
            '1',
            [$this->deploymentRepository, 'deleteDeployments']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
