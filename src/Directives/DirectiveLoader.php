<?php

namespace Templar\Directives;

/**
 * Auto-discovers and loads directive classes from the Directives folder.
 */
class DirectiveLoader
{
    /** @var array<string, class-string<DirectiveInterface>> */
    private static array $directives = [];

    /** @var bool */
    private static bool $loaded = false;

    /**
     * Get all registered directives.
     *
     * @return array<string, array{class: class-string<DirectiveInterface>, hasArguments: bool}>
     */
    public static function getDirectives(): array
    {
        self::loadDirectives();

        $result = [];
        foreach (self::$directives as $name => $class) {
            $result[$name] = [
                'class' => $class,
                'hasArguments' => $class::hasArguments(),
            ];
        }

        return $result;
    }

    /**
     * Get a specific directive by name.
     *
     * @param string $name
     * @return class-string<DirectiveInterface>|null
     */
    public static function get(string $name): ?string
    {
        self::loadDirectives();
        return self::$directives[$name] ?? null;
    }

    /**
     * Check if a directive exists.
     *
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        self::loadDirectives();
        return isset(self::$directives[$name]);
    }

    /**
     * Register a custom directive class.
     *
     * @param class-string<DirectiveInterface> $class
     * @throws \InvalidArgumentException
     */
    public static function register(string $class): void
    {
        if (!is_subclass_of($class, DirectiveInterface::class)) {
            throw new \InvalidArgumentException(
                "Directive class {$class} must implement DirectiveInterface"
            );
        }

        $name = $class::getName();

        if (isset(self::$directives[$name])) {
            throw new \InvalidArgumentException(
                "Directive '{$name}' is already registered"
            );
        }

        self::$directives[$name] = $class;
    }

    /**
     * Auto-discover and load all directives from the Directives folder.
     */
    private static function loadDirectives(): void
    {
        if (self::$loaded) {
            return;
        }

        $directory = __DIR__;
        $files = glob($directory . '/*Directive.php');

        foreach ($files as $file) {
            $filename = basename($file, '.php');

            // Skip the interface and loader
            if ($filename === 'DirectiveInterface' || $filename === 'DirectiveLoader') {
                continue;
            }

            $class = __NAMESPACE__ . '\\' . $filename;

            // Ensure class exists and implements interface
            if (class_exists($class) && is_subclass_of($class, DirectiveInterface::class)) {
                $name = $class::getName();
                self::$directives[$name] = $class;
            }
        }

        self::$loaded = true;
    }

    /**
     * Reset the loader (useful for testing).
     */
    public static function reset(): void
    {
        self::$directives = [];
        self::$loaded = false;
    }
}
