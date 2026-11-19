<?php

namespace SeQura\Core\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class StoreScopedRepositoryRule implements Rule
{
    private const REPOSITORIES = '/^SeQura\\\\Core\\\\BusinessLogic\\\\DataAccess\\\\[^\\\\]+\\\\Repositories\\\\/';

    private const STORE_CONTEXT = 'SeQura\\Core\\BusinessLogic\\Domain\\Multistore\\StoreContext';

    /**
     * @return string
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        if (!\preg_match(self::REPOSITORIES, $class->getName()) || !$this->touchesStoreId($node->getOriginalNode())) {
            return [];
        }

        if ($class->hasConstructor()) {
            foreach ($class->getConstructor()->getVariants()[0]->getParameters() as $parameter) {
                if (\in_array(self::STORE_CONTEXT, $parameter->getType()->getObjectClassNames(), true)) {
                    return [];
                }
            }
        }

        return [
            RuleErrorBuilder::message(
                \sprintf(
                    '%s reads or writes storeId but does not take StoreContext in its constructor. Store-scoped '
                    . 'repositories inject StoreContext, filter every query by storeContext->getStoreId() and '
                    . 'stamp setStoreId() on writes (.claude/docs/codingStandard.md §8).',
                    $class->getName()
                )
            )->identifier('sequra.storeScopedRepository')->build(),
        ];
    }

    /**
     * @param Node $class
     *
     * @return bool
     */
    private function touchesStoreId(Node $class): bool
    {
        return (new NodeFinder())->findFirst([$class], static function (Node $node): bool {
            if ($node instanceof String_) {
                return $node->value === 'storeId';
            }

            return $node instanceof MethodCall
                && $node->name instanceof Identifier
                && \in_array($node->name->toString(), ['setStoreId', 'getStoreId'], true);
        }) !== null;
    }
}
