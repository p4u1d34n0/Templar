<?php

namespace Templar\Directives;

class DateFormatDirective implements DirectiveInterface
{
    public static function getName(): string
    {
        return 'date';
    }

    public static function hasArguments(): bool
    {
        return true;
    }

    public static function handle(string $expression): string
    {
        // Support @date(value) or @date(value, 'format')
        return "<?php
            \$__parts = [{$expression}];
            \$__val = \$__parts[0] ?? null;
            \$__fmt = \$__parts[1] ?? 'Y-m-d';
            if (\$__val instanceof \DateTime) {
                echo \$__val->format(\$__fmt);
            } elseif (is_numeric(\$__val)) {
                echo date(\$__fmt, \$__val);
            } elseif (is_string(\$__val)) {
                echo date(\$__fmt, strtotime(\$__val));
            }
        ?>";
    }
}
