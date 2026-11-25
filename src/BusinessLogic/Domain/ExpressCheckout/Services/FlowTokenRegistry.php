<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutFlowTokenStorageInterface;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class FlowTokenRegistry
{
    private const TOKEN_LENGTH = 32;
    private const MAX_TOKENS = 8;
    private const TOKEN_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * @var ExpressCheckoutFlowTokenStorageInterface
     */
    protected $storage;

    /**
     * @param ExpressCheckoutFlowTokenStorageInterface $storage
     */
    public function __construct(ExpressCheckoutFlowTokenStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $cartId
     *
     * @return string
     *
     * @throws \Exception
     */
    public function mint(string $cartId): string
    {
        $token = $this->generateToken();

        $tokens = array_filter(
            $this->storage->getFlowTokens(),
            static function ($storedCartId) use ($cartId) {
                return $storedCartId !== $cartId;
            }
        );
        $tokens[$token] = $cartId;

        $this->storage->saveFlowTokens(\array_slice($tokens, -self::MAX_TOKENS, null, true));

        return $token;
    }

    /**
     * @param string $token
     *
     * @return string|null
     */
    public function resolve(string $token): ?string
    {
        $tokens = $this->storage->getFlowTokens();

        return \array_key_exists($token, $tokens) && \is_string($tokens[$token]) ? $tokens[$token] : null;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function generateToken(): string
    {
        $lastIndex = \strlen(self::TOKEN_ALPHABET) - 1;
        $token = '';
        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= self::TOKEN_ALPHABET[random_int(0, $lastIndex)];
        }

        return $token;
    }
}
