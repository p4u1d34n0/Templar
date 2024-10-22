<?php

namespace Templar;

use Templar\Directives;

class Compiler
{
    protected $directives = [];

    public function __construct()
    {
        $directives = new Directives();
        // Initialize with default or custom directives
        $this->directives = $directives->getDirectives();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Add a new directive.
     *
     * @param string $name The name of the directive.
     * @param callable $handler The handler that transforms the directive.
     */
    public function addDirective(string $name, callable $handler)
    {
        if (isset($this->directives[$name])) {
            throw new \Exception(message: "Directive $name already exists.");
        }

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
        $content = file_get_contents(filename: $template);

        return $this->compileFromString($content, $data);
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
        foreach ($this->directives as $directive => $attributes) {

            $pattern = "/@$directive/";
            if ($attributes['hasArguments']) {
                $pattern = "/@$directive\s*\((.*?)\)/";
            }

            $content = preg_replace_callback(pattern: $pattern, callback: function ($matches) use ($attributes): mixed {
                return $attributes['handler']($matches[1]);
            }, subject: $content);
        }

        // Execute the template with the passed data
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }
}
