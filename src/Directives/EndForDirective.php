<?php

namespace Templar\Directives;

class EndForDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'endfor';
    }

    public static function hasArguments(): bool
    {
        return false;
    }

    public static function handle(string $expression): string
    {
        return "<?php endfor; ?>";
    }
}
