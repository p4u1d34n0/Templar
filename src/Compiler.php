<?php

namespace Templar;

use Templar\Directives;
use Templar\Dumper;

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
        // Compile echo syntax: % variable % or % object.property %
        $content = $this->compileEchoSyntax($content);

        // Compile dump syntax: %! variable !% or %! object.property !%
        $content = $this->compileDumpSyntax($content);

        // Apply directives to content string
        foreach ($this->directives as $directive => $attributes) {

            $pattern = "/@$directive/";
            if ($attributes['hasArguments']) {
                $pattern = "/@$directive\s*\((.*?)\)/s";
            }

            $content = preg_replace_callback(pattern: $pattern, callback: function ($matches) use ($attributes): mixed {
                return $attributes['handler']($matches[1] ?? '');
            }, subject: $content);
        }

        // Execute the template with the passed data
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }

    /**
     * Compile the % variable % echo syntax.
     *
     * Supports:
     *   % name %           -> $name
     *   % user.name %      -> $user['name'] or $user->name
     *   % items.0.title %  -> $items[0]['title']
     *
     * @param string $content
     * @return string
     */
    protected function compileEchoSyntax(string $content): string
    {
        // Match % variable % or % object.property.nested %
        return preg_replace_callback(
            '/%\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z0-9_]+)*)\s*%/',
            function ($matches) {
                $variable = $matches[1];
                $accessor = $this->compileVariableAccessor($variable);
                return '<?php echo htmlspecialchars((string)(' . $accessor . ' ?? \'\')); ?>';
            },
            $content
        );
    }

    /**
     * Compile the %! variable !% intelligent dump syntax.
     *
     * Outputs based on variable type:
     *   - String: raw output
     *   - Array/Object: formatted dump
     *   - Closure: signature info
     *
     * @param string $content
     * @return string
     */
    protected function compileDumpSyntax(string $content): string
    {
        // Match %! variable !% or %! object.property.nested !%
        return preg_replace_callback(
            '/%!\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z0-9_]+)*)\s*!%/',
            function ($matches) {
                $variable = $matches[1];
                $accessor = $this->compileVariableAccessor($variable);
                return '<?php echo \Templar\Dumper::dump(' . $accessor . ' ?? null); ?>';
            },
            $content
        );
    }

    /**
     * Convert dot notation to PHP variable accessor.
     *
     * Examples:
     *   name         -> $name
     *   user.name    -> (is_array($user) ? ($user['name'] ?? null) : ($user->name ?? null))
     *   items.0.id   -> Smart array/object access
     *
     * @param string $variable Dot-notation variable path
     * @return string PHP accessor code
     */
    protected function compileVariableAccessor(string $variable): string
    {
        $parts = explode('.', $variable);

        if (count($parts) === 1) {
            return '$' . $variable;
        }

        // Build a smart accessor that handles both arrays and objects
        $base = '$' . array_shift($parts);
        $accessor = $base;

        foreach ($parts as $part) {
            // Check if it's a numeric index
            if (is_numeric($part)) {
                $accessor = '(is_array(' . $accessor . ') ? (' . $accessor . '[' . $part . '] ?? null) : null)';
            } else {
                // Could be array key or object property
                $accessor = '(is_array(' . $accessor . ') ? (' . $accessor . '[\'' . $part . '\'] ?? null) : (is_object(' . $accessor . ') ? (' . $accessor . '->' . $part . ' ?? null) : null))';
            }
        }

        return $accessor;
    }
}
