<?php

namespace Templar\Directives;

class JsonDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'json';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        return "<?php echo json_encode({$expression}, JSON_PRETTY_PRINT); ?>";
    }
}
