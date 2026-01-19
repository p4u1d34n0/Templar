<?php

namespace Templar\Directives;

class EndForeachDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'endforeach';
    }

    public static function hasArguments(): bool
    {
        return false;
    }

    public static function handle(string $expression): string
    {
        return "<?php endforeach; ?>";
    }
}
