<?php

namespace SeQura\Core\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<New_>
 */
final class NoOuterLayerInDomainRule implements Rule
{
    private const DOMAIN = 'SeQura\\Core\\BusinessLogic\\Domain';

    private const OUTER_LAYERS = [
        'SeQura\\Core\\BusinessLogic\\DataAccess\\',
        'SeQura\\Core\\BusinessLogic\\SeQuraAPI\\',
        'SeQura\\Core\\BusinessLogic\\AdminAPI\\',
        'SeQura\\Core\\BusinessLogic\\CheckoutAPI\\',
        'SeQura\\Core\\BusinessLogic\\WebhookAPI\\',
        'SeQura\\Core\\BusinessLogic\\ConfigurationWebhookAPI\\',
    ];

    private const HTTP_CLIENTS = '/^SeQura\\\\Core\\\\Infrastructure\\\\Http\\\\\\w*HttpClient$/i';

    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace() ?? '';
        $inDomain = $namespace === self::DOMAIN || \strpos($namespace, self::DOMAIN . '\\') === 0;
        if (!$inDomain || !$node->class instanceof Name) {
            return [];
        }

        $class = $scope->resolveName($node->class);
        if (\preg_match(self::HTTP_CLIENTS, $class)) {
            return [
                RuleErrorBuilder::message(
                    'Domain must not instantiate an HTTP client: SeQura HTTP calls go through a SeQuraAPI proxy '
                    . 'behind a Domain ProxyContracts interface, injected through the constructor '
                    . '(.claude/docs/codingStandard.md §2).'
                )->identifier('sequra.domainOuterLayer')->build(),
            ];
        }

        foreach (self::OUTER_LAYERS as $layer) {
            if (\strpos($class, $layer) === 0) {
                return [
                    RuleErrorBuilder::message(
                        \sprintf(
                            'Domain must not instantiate %s: take the contract the Domain owns '
                            . '(RepositoryContracts, ProxyContracts or Domain/Integration) as a constructor '
                            . 'parameter and wire the concrete class in BootstrapComponent '
                            . '(.claude/docs/codingStandard.md §2 and §4).',
                            $class
                        )
                    )->identifier('sequra.domainOuterLayer')->build(),
                ];
            }
        }

        return [];
    }
}
