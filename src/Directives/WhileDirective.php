<?php

namespace Templar\Directives;

class WhileDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'while';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php while({$expression}): ?>";
    }
}
