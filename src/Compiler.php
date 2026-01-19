<?php

namespace Templar;

use Templar\Directives\DirectiveLoader;
use Templar\Directives\DirectiveInterface;
use Templar\Dumper;

class Compiler
{
    /** @var array<string, array{class: class-string<DirectiveInterface>, hasArguments: bool}|array{handler: callable, hasArguments: bool}> */
    protected array $directives = [];

    public function __construct()
    {
        // Load directives from the Directives folder
        $this->directives = DirectiveLoader::getDirectives();
    }

    /**
     * Add a custom directive via closure.
     *
     * @param string $name The directive name (without @).
     * @param callable $handler The handler that returns compiled PHP code.
     * @param bool $hasArguments Whether the directive accepts arguments.
     */
    public function addDirective(string $name, callable $handler, bool $hasArguments = true): void
    {
        if (isset($this->directives[$name])) {
            throw new \InvalidArgumentException("Directive '{$name}' already exists.");
        }

        $this->directives[$name] = [
            'handler' => $handler,
            'hasArguments' => $hasArguments,
        ];
    }

    /**
     * Register a directive class.
     *
     * @param class-string<DirectiveInterface> $class
     */
    public function addDirectiveClass(string $class): void
    {
        DirectiveLoader::register($class);
        $name = $class::getName();
        $this->directives[$name] = [
            'class' => $class,
            'hasArguments' => $class::hasArguments(),
        ];
    }

    /**
     * Compile a template file.
     *
     * @param string $template Path to the template file.
     * @param array $data Variables to be passed to the template.
     * @return string Compiled output.
     */
    public function compile(string $template, array $data = []): string
    {
        $content = file_get_contents($template);

        return $this->compileFromString($content, $data);
    }

    /**
     * Compile a template string.
     *
     * @param string $content The template content.
     * @param array $data Variables to be passed to the template.
     * @return string Compiled output.
     */
    public function compileFromString(string $content, array $data = []): string
    {
        // Compile echo syntax: % variable %
        $content = $this->compileEchoSyntax($content);

        // Compile dump syntax: %! variable !%
        $content = $this->compileDumpSyntax($content);

        // Compile directives
        $content = $this->compileDirectives($content);

        // Execute the template
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
    }

    /**
     * Compile all directives in the content.
     *
     * @param string $content
     * @return string
     */
    protected function compileDirectives(string $content): string
    {
        foreach ($this->directives as $name => $config) {
            $hasArguments = $config['hasArguments'];

            if ($hasArguments) {
                $pattern = "/@{$name}\s*\((.*?)\)/s";
            } else {
                $pattern = "/@{$name}(?!\w)/";
            }

            $content = preg_replace_callback(
                $pattern,
                function ($matches) use ($config) {
                    $expression = $matches[1] ?? '';

                    // Check if it's a class-based directive or closure
                    if (isset($config['class'])) {
                        return $config['class']::handle($expression);
                    } else {
                        return $config['handler']($expression);
                    }
                },
                $content
            );
        }

        return $content;
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
     * @param string $content
     * @return string
     */
    protected function compileDumpSyntax(string $content): string
    {
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
     * @param string $variable Dot-notation variable path
     * @return string PHP accessor code
     */
    protected function compileVariableAccessor(string $variable): string
    {
        $parts = explode('.', $variable);

        if (count($parts) === 1) {
            return '$' . $variable;
        }

        $base = '$' . array_shift($parts);
        $accessor = $base;

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $accessor = '(is_array(' . $accessor . ') ? (' . $accessor . '[' . $part . '] ?? null) : null)';
            } else {
                $accessor = '(is_array(' . $accessor . ') ? (' . $accessor . '[\'' . $part . '\'] ?? null) : (is_object(' . $accessor . ') ? (' . $accessor . '->' . $part . ' ?? null) : null))';
            }
        }

        return $accessor;
    }
}
