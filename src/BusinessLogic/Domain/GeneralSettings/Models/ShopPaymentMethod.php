<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyShopPaymentMethodParameterException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class ShopPaymentMethod
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models
 */
class ShopPaymentMethod
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $code
     * @param string $name
     *
     * @throws EmptyShopPaymentMethodParameterException
     */
    public function __construct(string $code, string $name)
    {
        if(empty($code) || empty($name)) {
            throw new EmptyShopPaymentMethodParameterException(
                new TranslatableLabel('No parameter can be an empty string.','general.errors.empty')
            );
        }

        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
