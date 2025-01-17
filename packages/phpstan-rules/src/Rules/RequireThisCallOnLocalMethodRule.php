<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\Astral\NodeFinder\SimpleNodeFinder;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\RequireThisCallOnLocalMethodRule\RequireThisCallOnLocalMethodRuleTest
 */
final class RequireThisCallOnLocalMethodRule implements Rule, DocumentedRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Use "$this-><method>()" instead of "self::<method>()" to call local method';

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private SimpleNodeFinder $simpleNodeFinder
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->simpleNameResolver->isName($node->class, 'self')) {
            return [];
        }

        $classMethod = $this->getClassMethodInCurrentClass($node);
        if (! $classMethod instanceof ClassMethod) {
            return [];
        }

        if ($classMethod->isStatic()) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        self::execute();
    }

    private function execute()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->execute();
    }

    private function execute()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    private function getClassMethodInCurrentClass(StaticCall $staticCall): ?ClassMethod
    {
        $class = $this->simpleNodeFinder->findFirstParentByType($staticCall, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        $staticCallName = $this->simpleNameResolver->getName($staticCall->name);
        if ($staticCallName === null) {
            return null;
        }

        return $class->getMethod($staticCallName);
    }
}
