<?php

namespace Templar\Directives;

class ForeachDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'foreach';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php foreach({$expression}): ?>";
    }
}
