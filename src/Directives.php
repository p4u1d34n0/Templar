<?php

namespace Templar;

use Templar\Compiler;

class Directives
{


    protected $directives = [];

    /**
     * Registers custom directives for the templating engine.
     *
     * This method registers the following directives:
     */
    public function __construct()
    {

        // Uppercase directive
        $this->add(
            name: 'uppercase',
            handler: function ($expression): string {
                return "<?php echo strtoupper($expression); ?>";
            },
            hasArguments: true
        );

        // Date formatting directive
        $this->add(
            name: 'dateformat',
            handler: function ($expression): string {
                return "<?php echo date('Y-m-d', strtotime($expression)); ?>";
            },
            hasArguments: true
        );

        // Custom dump directive for debugging
        $this->add(
            name: 'dump',
            handler: function ($expression): string {
                return "<?php var_dump($expression); ?>";
            },
            hasArguments: true
        );

        // foreach directive
        $this->add(
            name: 'foreach',
            handler: function ($expression): string {
                return "<?php foreach($expression): ?>";
            },
            hasArguments: true
        );

        // endforeach directive
        $this->add(
            name: 'endforeach',
            handler: function ($expression): string {
                return "<?php endforeach; ?>";
            },
            hasArguments: false
        );

        // if directive
        $this->add(
            name: 'if',
            handler: function ($expression): string {
                return "<?php if($expression): ?>";
            },
            hasArguments: true
        );

        // else directive
        $this->add(
            name: 'else',
            handler: function ($expression): string {
                return "<?php else: ?>";
            },
            hasArguments: false
        );

        // endif directive
        $this->add(
            name: 'endif',
            handler: function ($expression): string {
                return "<?php endif; ?>";
            },
            hasArguments: false
        );

        // while directive
        $this->add(
            name: 'while',
            handler: function ($expression): string {
                return "<?php while($expression): ?>";
            },
            hasArguments: true
        );

        // endwhile directive
        $this->add(
            name: 'endwhile',
            handler: function ($expression): string {
                return "<?php endwhile; ?>";
            },
            hasArguments: false
        );

        // do directive
        $this->add(
            name: 'do',
            handler: function ($expression): string {
                return "<?php do { ?>";
            },
            hasArguments: false
        );

        // while directive
        $this->add(
            name: 'dowhile',
            handler: function ($expression): string {
                return "<?php } while($expression); ?>";
            },
            hasArguments: true
        );
    }

    /**
     * add a  directives.
     *
     */
    private function add(
        string $name,
        \Closure $handler,
        bool $hasArguments = false
    ): void {

        if (isset($this->directives[$name])) {
            throw new \Exception(message: "Directive $name already exists.");
        }

        $this->directives[$name] = [
            'handler' => $handler,
            'hasArguments' => $hasArguments
        ];
    }

    /**
     * Get the registered directives.
     *
     * @return array Registered directives.
     */
    public function getDirectives()
    {
        return $this->directives;
    }
}
