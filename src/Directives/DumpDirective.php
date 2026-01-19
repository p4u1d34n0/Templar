<?php

namespace Templar\Directives;

class DumpDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'dump';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php var_dump({$expression}); ?>";
    }
}
