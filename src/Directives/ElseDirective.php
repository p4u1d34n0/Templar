<?php

namespace Templar\Directives;

class ElseDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'else';
    }

    public static function hasArguments(): bool
    {
        return false;
    }

    public static function handle(string $expression): string
    {
        return "<?php else: ?>";
    }
}
