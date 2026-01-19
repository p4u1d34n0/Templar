<?php

namespace Templar\Directives;

class EndIfDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'endif';
    }

    public static function hasArguments(): bool
    {
        return false;
    }

    public static function handle(string $expression): string
    {
        return "<?php endif; ?>";
    }
}
