<?php

namespace Templar\Directives;

class UppercaseDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'uppercase';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php echo strtoupper({$expression}); ?>";
    }
}
