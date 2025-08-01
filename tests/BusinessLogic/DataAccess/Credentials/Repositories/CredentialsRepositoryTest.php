<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Credentials\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities\Credentials as CredentialsEntity;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Repositories\CredentialsRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CredentialsRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Credentials\Repositories
 */
class CredentialsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var CredentialsRepositoryInterface */
    private $credentialsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(CredentialsEntity::getClassName());
        $this->credentialsRepository = new CredentialsRepository(
            TestRepositoryRegistry::getRepository(CredentialsEntity::getClassName()),
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(CredentialsRepositoryInterface::class, function () {
            return $this->credentialsRepository;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCredentialsNoCredentials(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentials']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetCredentials(): void
    {
        // arrange
        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea'),
        ];

        foreach ($credentials as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('1');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentials']
        );

        // assert
        self::assertCount(4, $result);
        self::assertEquals($credentials, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetCredentialsDifferentStores(): void
    {
        // arrange
        $credentialsStore1 = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
        ];

        $credentialsStore2 = [
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea'),
        ];

        foreach ($credentialsStore1 as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('1');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        foreach ($credentialsStore2 as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('2');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        // act
        $result1 = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentials']
        );
        $result2 = StoreContext::doWithStore(
            '2',
            [$this->credentialsRepository, 'getCredentials']
        );

        // assert
        self::assertCount(2, $result1);
        self::assertEquals($credentialsStore1, $result1);
        self::assertCount(2, $result2);
        self::assertEquals($credentialsStore2, $result2);
    }

    /**
     * @throws Exception
     */
    public function testSetCredentials(): void
    {
        // arrange
        $credentials1 = new Credentials(
            'logeecom1',
            'PT',
            'EUR',
            'assetsKey1',
            [],
            'sequra'
        );
        $credentials2 = new Credentials(
            'logeecom2',
            'FR',
            'EUR',
            'assetsKey2',
            [],
            'sequra'
        );
        $credentials3 = new Credentials(
            'logeecom3',
            'IT',
            'EUR',
            'assetsKey3',
            [],
            'svea'
        );
        $credentials4 = new Credentials(
            'logeecom4',
            'ES',
            'EUR',
            'assetsKey4',
            [],
            'svea'
        );

        $credentials = [$credentials1, $credentials2, $credentials3, $credentials4];

        // act
        StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'setCredentials'],
            [$credentials]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($credentials1, $savedEntity[0]->getCredentials());
        self::assertEquals($credentials2, $savedEntity[1]->getCredentials());
        self::assertEquals($credentials3, $savedEntity[2]->getCredentials());
        self::assertEquals($credentials4, $savedEntity[3]->getCredentials());
    }

    /**
     * @throws Exception
     */
    public function testUpdateCredentials(): void
    {
        // arrange
        $initialCredentials = new Credentials(
            'logeecom1',
            'PT',
            'EUR',
            'assetsKey1',
            [],
            'sequra'
        );
        $updatedCredentials = new Credentials(
            'logeecom1',
            'PT',
            'EUR',
            'assetsKey11',
            [],
            'svea'
        );

        $credentials = [$initialCredentials];
        $credentialsAfterUpdate = [$updatedCredentials];

        // act
        StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'setCredentials'],
            [$credentials]
        );
        StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'setCredentials'],
            [$credentialsAfterUpdate]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($updatedCredentials, $savedEntity[0]->getCredentials());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteCredentials(): void
    {
        // arrange
        $credentials = [
            new Credentials(
                'logeecom1',
                'PT',
                'EUR',
                'assetsKey1',
                [],
                'sequra'
            ),
            new Credentials(
                'logeecom2',
                'FR',
                'EUR',
                'assetsKey2',
                [],
                'sequra'
            ),
            new Credentials(
                'logeecom3',
                'IT',
                'EUR',
                'assetsKey3',
                [],
                'svea'
            ),
            new Credentials(
                'logeecom4',
                'ES',
                'EUR',
                'assetsKey4',
                [],
                'svea'
            )
        ];

        foreach ($credentials as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('1');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        // act
        StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'deleteCredentialsByDeploymentId'],
            ['sequra']
        );

        // assert
        $entities = $this->repository->select();
        self::assertCount(2, $entities);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCredentialsByCountryCode(): void
    {
        // arrange
        $portugueseCredentials = new Credentials(
            'logeecom1',
            'PT',
            'EUR',
            'assetsKey1',
            [],
            'svea'
        );
        $frenchCredentials = new Credentials(
            'logeecom2',
            'FR',
            'EUR',
            'assetsKey2',
            [],
            'svea'
        );
        $italianCredentials = new Credentials(
            'logeecom3',
            'IT',
            'EUR',
            'assetsKey3',
            [],
            'svea'
        );
        $spanishCredentials = new Credentials(
            'logeecom4',
            'ES',
            'EUR',
            'assetsKey4',
            [],
            'sequra'
        );

        $credentials = [$portugueseCredentials, $frenchCredentials, $italianCredentials, $spanishCredentials];

        foreach ($credentials as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('1');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        // act
        $resultFR = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByCountryCode'],
            ['FR']
        );
        $resultPT = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByCountryCode'],
            ['PT']
        );
        $resultIT = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByCountryCode'],
            ['IT']
        );
        $resultES = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByCountryCode'],
            ['ES']
        );
        $resultRS = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByCountryCode'],
            ['RS']
        );

        // assert
        self::assertEquals($resultFR, $frenchCredentials);
        self::assertEquals($resultES, $spanishCredentials);
        self::assertEquals($resultIT, $italianCredentials);
        self::assertEquals($resultPT, $portugueseCredentials);
        self::assertNull($resultRS);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCredentialsByMerchantIdNoCredentials(): void
    {
        // arrange

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetCredentialsByMerchantId(): void
    {
        // arrange
        $portugueseCredentials = new Credentials(
            'logeecom1',
            'PT',
            'EUR',
            'assetsKey1',
            [],
            'svea'
        );
        $frenchCredentials = new Credentials(
            'logeecom2',
            'FR',
            'EUR',
            'assetsKey2',
            [],
            'svea'
        );
        $italianCredentials = new Credentials(
            'logeecom3',
            'IT',
            'EUR',
            'assetsKey3',
            [],
            'svea'
        );
        $spanishCredentials = new Credentials(
            'logeecom4',
            'ES',
            'EUR',
            'assetsKey4',
            [],
            'sequra'
        );

        $credentials = [$portugueseCredentials, $frenchCredentials, $italianCredentials, $spanishCredentials];

        foreach ($credentials as $credential) {
            $entity = new CredentialsEntity();
            $entity->setCredentials($credential);
            $entity->setStoreId('1');
            $entity->setMerchantId($credential->getMerchantId());
            $entity->setCountry($credential->getCountry());

            $this->repository->save($entity);
        }

        // act
        $resultFR = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom2']
        );
        $resultPT = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom1']
        );
        $resultIT = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom3']
        );
        $resultES = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom4']
        );
        $resultRS = StoreContext::doWithStore(
            '1',
            [$this->credentialsRepository, 'getCredentialsByMerchantId'],
            ['logeecom5']
        );

        // assert
        self::assertEquals($resultFR, $frenchCredentials);
        self::assertEquals($resultES, $spanishCredentials);
        self::assertEquals($resultIT, $italianCredentials);
        self::assertEquals($resultPT, $portugueseCredentials);
        self::assertNull($resultRS);
    }
}
