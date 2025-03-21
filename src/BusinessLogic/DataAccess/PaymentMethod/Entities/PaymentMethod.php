<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Entities;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class PaymentMethod
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PaymentMethod
 */
class PaymentMethod extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $merchantId;
    /**
     * @var string
     */
    protected $product;
    /**
     * @var SeQuraPaymentMethod
     */
    protected $seQuraPaymentMethod;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('merchantId');
        $indexMap->addStringIndex('product');

        return new EntityConfiguration($indexMap, 'PaymentMethod');
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->product = $data['product'];
        $this->merchantId = $data['merchantId'];
        $this->seQuraPaymentMethod = SeQuraPaymentMethod::fromArray($data['seQuraPaymentMethod']);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['merchantId'] = $this->merchantId;
        $data['product'] = $this->product;
        $data['seQuraPaymentMethod'] = $this->seQuraPaymentMethod->toArray();

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string|null
     */
    public function getProduct(): ?string
    {
        return $this->product;
    }

    /**
     * @param  string $product
     *
     * @return void
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @return SeQuraPaymentMethod|null
     */
    public function getSeQuraPaymentMethod(): ?SeQuraPaymentMethod
    {
        return $this->seQuraPaymentMethod;
    }

    /**
     * @param SeQuraPaymentMethod $seQuraPaymentMethod
     *
     * @return void
     */
    public function setSeQuraPaymentMethod(SeQuraPaymentMethod $seQuraPaymentMethod): void
    {
        $this->seQuraPaymentMethod = $seQuraPaymentMethod;
    }
}
