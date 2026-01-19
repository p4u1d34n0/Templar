<?php

namespace Templar\Directives;

class ForDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'for';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php for({$expression}): ?>";
    }
}
