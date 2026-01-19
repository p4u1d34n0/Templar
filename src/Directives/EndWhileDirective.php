<?php

namespace Templar\Directives;

class EndWhileDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'endwhile';
    }

    public static function hasArguments(): bool
    {
        return false;
    }

    public static function handle(string $expression): string
    {
        return "<?php endwhile; ?>";
    }
}
