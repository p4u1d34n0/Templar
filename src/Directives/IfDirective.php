<?php

namespace Templar\Directives;

class IfDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'if';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php if({$expression}): ?>";
    }
}
