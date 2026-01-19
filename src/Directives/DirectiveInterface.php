<?php

namespace Templar\Directives;

/**
 * Interface for all Templar directives.
 *
 * Each directive is a self-contained class that handles
 * compilation of a specific template directive.
 */
interface DirectiveInterface
{
    /**
     * Get the directive name (without @ prefix).
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Whether this directive accepts arguments.
     *
     * @return bool
     */
    public static function hasArguments(): bool;

    /**
     * Compile the directive into PHP code.
     *
     * @param string $expression The expression passed to the directive
     * @return string The compiled PHP code
     */
    public static function handle(string $expression): string;
}
