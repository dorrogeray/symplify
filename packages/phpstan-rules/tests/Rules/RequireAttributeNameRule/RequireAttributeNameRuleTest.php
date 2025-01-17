<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\RequireAttributeNameRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\RequireAttributeNameRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<RequireAttributeNameRule>
 */
final class RequireAttributeNameRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorsWithLines
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/MissingName.php', [[RequireAttributeNameRule::ERROR_MESSAGE, 11]]];
        yield [__DIR__ . '/Fixture/SkipCorrectName.php', []];
        yield [__DIR__ . '/Fixture/SkipDefaultName.php', []];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(RequireAttributeNameRule::class, __DIR__ . '/config/configured_rule.neon');
    }
}
