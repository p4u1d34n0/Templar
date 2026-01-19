<?php

namespace Templar\Directives;

class ElseIfDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'elseif';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php elseif({$expression}): ?>";
    }
}
