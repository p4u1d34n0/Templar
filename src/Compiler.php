<?php

namespace Templar;

class Compiler
{
    protected $directives = [];

    public function __construct(array $directives = [])
    {
        // Initialize with default or custom directives
        $this->directives = $directives;
    }

    /**
     * Add a new directive.
     *
     * @param string $name The name of the directive.
     * @param callable $handler The handler that transforms the directive.
     */
    public function addDirective(string $name, callable $handler)
    {
        $this->directives[$name] = $handler;
    }

    /**
     * Compile a template by applying directives and transforming the template code.
     *
     * @param string $template Path to the template file.
     * @param array $data Variables to be extracted and used in the template.
     * @return string Compiled output.
     */
    public function compile(string $template, array $data = []): string
    {
        $content = file_get_contents($template);

        // Apply registered directives
        foreach (self::$directives as $directive => $handler) {
            $pattern = "/@$directive\s*\((.*?)\)/";
            $content = preg_replace_callback($pattern, function ($matches) use ($handler) {
                return $handler($matches[1]);
            }, $content);
        }

        // Now, execute the template with the passed data
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }

    /**
     * Render a template from raw content (string), useful when dynamic templates are generated.
     *
     * @param string $content The template content as a string.
     * @param array $data Variables to be extracted and used in the template.
     * @return string Compiled output.
     */
    public function compileFromString(string $content, array $data = []): string
    {
        // Apply directives to content string
        foreach ($this->directives as $directive => $handler) {
            $pattern = "/@$directive\s*\((.*?)\)/";
            $content = preg_replace_callback($pattern, function ($matches) use ($handler) {
                return $handler($matches[1]);
            }, $content);
        }

        // Execute the template with the passed data
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }

    public static function directive(string $name, callable $handler): void
    {
        // Register a new directive
        static::$directives[$name] = $handler;
    }
}
