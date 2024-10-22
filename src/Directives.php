<?php

namespace Templar;

use \Templar\Compiler;

class Directives
{
    /**
     * Registers custom directives for the templating engine.
     *
     * This method registers the following directives:
     */
    public static function register()
    {
        // Uppercase directive
        Compiler::directive('uppercase', function ($expression) {
            return "<?php echo strtoupper($expression); ?>";
        });

        // Date formatting directive
        Compiler::directive('dateformat', function ($expression) {
            return "<?php echo date('Y-m-d', strtotime($expression)); ?>";
        });

        // Custom dump directive for debugging
        Compiler::directive('dump', function ($expression) {
            return "<?php var_dump($expression); ?>";
        });
    }
}
