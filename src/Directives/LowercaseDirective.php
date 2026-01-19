<?php

namespace Templar\Directives;

class LowercaseDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'lowercase';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php echo strtolower({$expression}); ?>";
    }
}
