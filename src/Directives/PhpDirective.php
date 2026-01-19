<?php

namespace Templar\Directives;

class PhpDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'php';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php {$expression}; ?>";
    }
}
