<?php

namespace Templar;

/**
 * Intelligent variable dumper for Templar templates.
 *
 * Outputs variables based on their type:
 * - Strings: raw output
 * - Numbers: formatted output
 * - Arrays/Objects: formatted dump (like dd but no die)
 * - Closures: signature and location info
 */
class Dumper
{
    private static bool $stylesIncluded = false;

    /**
     * Dump a variable with intelligent formatting based on type.
     *
     * @param mixed $var The variable to dump
     * @param bool $includeStyles Include CSS styles (first call only)
     * @return string HTML output
     */
    public static function dump(mixed $var, bool $includeStyles = true): string
    {
        $output = '';

        // Include styles once per page
        if ($includeStyles && !self::$stylesIncluded) {
            $output .= self::getStyles();
            self::$stylesIncluded = true;
        }

        if (is_null($var)) {
            return $output . self::wrap('null', 'null');
        }

        if (is_bool($var)) {
            return $output . self::wrap($var ? 'true' : 'false', 'bool');
        }

        if (is_string($var)) {
            return $output . $var; // Raw string output
        }

        if (is_int($var)) {
            return $output . self::wrap((string)$var, 'int');
        }

        if (is_float($var)) {
            return $output . self::wrap((string)$var, 'float');
        }

        if (is_array($var)) {
            return $output . self::dumpArray($var);
        }

        if (is_object($var)) {
            if ($var instanceof \Closure) {
                return $output . self::dumpClosure($var);
            }
            return $output . self::dumpObject($var);
        }

        if (is_resource($var)) {
            return $output . self::wrap('resource(' . get_resource_type($var) . ')', 'resource');
        }

        return $output . self::wrap(gettype($var), 'unknown');
    }

    /**
     * Dump an array with recursive formatting.
     */
    private static function dumpArray(array $arr, int $depth = 0): string
    {
        if ($depth > 10) {
            return self::wrap('*MAX DEPTH*', 'warning');
        }

        $count = count($arr);
        $html = '<div class="templar-dump array">';
        $html .= '<span class="type clickable" onclick="this.parentElement.classList.toggle(\'collapsed\')">array(' . $count . ')</span>';

        if ($count === 0) {
            $html .= ' <span class="empty">[]</span>';
        } else {
            $html .= '<div class="items">';

            $i = 0;
            foreach ($arr as $key => $value) {
                $isLast = (++$i === $count);
                $prefix = $isLast ? '└─' : '├─';

                $html .= '<div class="item">';
                $html .= '<span class="prefix">' . $prefix . '</span> ';
                $html .= '<span class="key">' . htmlspecialchars((string)$key) . '</span>: ';
                $html .= self::dumpValue($value, $depth + 1);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Dump an object with property inspection.
     */
    private static function dumpObject(object $obj, int $depth = 0): string
    {
        if ($depth > 10) {
            return self::wrap('*MAX DEPTH*', 'warning');
        }

        $class = get_class($obj);
        $html = '<div class="templar-dump object">';
        $html .= '<span class="type clickable" onclick="this.parentElement.classList.toggle(\'collapsed\')">' . htmlspecialchars($class) . '</span>';

        // Get all properties including private/protected
        $reflection = new \ReflectionClass($obj);
        $properties = $reflection->getProperties();

        if (empty($properties)) {
            $html .= ' <span class="empty">{}</span>';
        } else {
            $html .= '<div class="properties">';

            $count = count($properties);
            $i = 0;

            foreach ($properties as $prop) {
                $prop->setAccessible(true);
                $isLast = (++$i === $count);
                $prefix = $isLast ? '└─' : '├─';

                $visibility = '';
                if ($prop->isPrivate()) $visibility = 'private ';
                elseif ($prop->isProtected()) $visibility = 'protected ';

                $value = $prop->isInitialized($obj) ? $prop->getValue($obj) : null;

                $html .= '<div class="prop">';
                $html .= '<span class="prefix">' . $prefix . '</span> ';
                $html .= '<span class="visibility">' . $visibility . '</span>';
                $html .= '<span class="key">' . htmlspecialchars($prop->getName()) . '</span>: ';
                $html .= self::dumpValue($value, $depth + 1);
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Dump a Closure with signature information.
     */
    private static function dumpClosure(\Closure $closure): string
    {
        $ref = new \ReflectionFunction($closure);
        $params = [];

        foreach ($ref->getParameters() as $param) {
            $p = '';

            if ($param->hasType()) {
                $type = $param->getType();
                $p .= ($type instanceof \ReflectionNamedType ? $type->getName() : (string)$type) . ' ';
            }

            $p .= '$' . $param->getName();

            if ($param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                $p .= ' = ' . self::formatValue($default);
            }

            $params[] = $p;
        }

        $returnType = '';
        if ($ref->hasReturnType()) {
            $type = $ref->getReturnType();
            $returnType = ': ' . ($type instanceof \ReflectionNamedType ? $type->getName() : (string)$type);
        }

        $html = '<div class="templar-dump closure">';
        $html .= '<span class="type">Closure</span>';
        $html .= '<span class="signature">(' . htmlspecialchars(implode(', ', $params)) . ')' . htmlspecialchars($returnType) . '</span>';
        $html .= '<div class="location">' . htmlspecialchars($ref->getFileName() . ':' . $ref->getStartLine()) . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Dump a value inline (for nested structures).
     */
    private static function dumpValue(mixed $value, int $depth = 0): string
    {
        if (is_null($value)) {
            return self::wrap('null', 'null');
        }

        if (is_bool($value)) {
            return self::wrap($value ? 'true' : 'false', 'bool');
        }

        if (is_string($value)) {
            $display = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
            return self::wrap('"' . htmlspecialchars($display) . '"', 'string');
        }

        if (is_int($value)) {
            return self::wrap((string)$value, 'int');
        }

        if (is_float($value)) {
            return self::wrap((string)$value, 'float');
        }

        if (is_array($value)) {
            return self::dumpArray($value, $depth);
        }

        if (is_object($value)) {
            if ($value instanceof \Closure) {
                return self::dumpClosure($value);
            }
            return self::dumpObject($value, $depth);
        }

        return self::wrap(gettype($value), 'unknown');
    }

    /**
     * Format a simple value for display.
     */
    private static function formatValue(mixed $value): string
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_string($value)) return '"' . $value . '"';
        if (is_array($value)) return '[...]';
        if (is_object($value)) return get_class($value);
        return (string)$value;
    }

    /**
     * Wrap a value in a styled span.
     */
    private static function wrap(string $value, string $type): string
    {
        return '<span class="templar-val templar-' . $type . '">' . $value . '</span>';
    }

    /**
     * Get the CSS styles for dump output.
     */
    public static function getStyles(): string
    {
        return <<<'CSS'
<style>
.templar-dump {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, monospace;
    font-size: 13px;
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 12px 16px;
    border-radius: 6px;
    margin: 8px 0;
    line-height: 1.5;
    overflow-x: auto;
}
.templar-dump .type {
    color: #4ec9b0;
    font-weight: 600;
}
.templar-dump .type.clickable {
    cursor: pointer;
}
.templar-dump .type.clickable:hover {
    text-decoration: underline;
}
.templar-dump.collapsed > .items,
.templar-dump.collapsed > .properties {
    display: none;
}
.templar-dump.collapsed > .type::after {
    content: ' ...';
    color: #6a9955;
}
.templar-dump .key {
    color: #9cdcfe;
}
.templar-dump .visibility {
    color: #c586c0;
    font-style: italic;
}
.templar-dump .prefix {
    color: #555;
}
.templar-dump .empty {
    color: #6a9955;
}
.templar-dump .items,
.templar-dump .properties {
    margin-left: 8px;
    padding-left: 12px;
    border-left: 1px solid #333;
    margin-top: 4px;
}
.templar-dump .item,
.templar-dump .prop {
    margin: 2px 0;
}
.templar-dump.closure .signature {
    color: #dcdcaa;
    margin-left: 4px;
}
.templar-dump.closure .location {
    color: #6a9955;
    font-size: 11px;
    margin-top: 4px;
}
.templar-val.templar-string { color: #ce9178; }
.templar-val.templar-int { color: #b5cea8; }
.templar-val.templar-float { color: #b5cea8; }
.templar-val.templar-bool { color: #569cd6; }
.templar-val.templar-null { color: #6a9955; font-style: italic; }
.templar-val.templar-warning { color: #f44747; font-weight: bold; }
.templar-val.templar-resource { color: #c586c0; }
.templar-val.templar-unknown { color: #808080; }
</style>
CSS;
    }

    /**
     * Reset styles flag (useful for testing).
     */
    public static function resetStyles(): void
    {
        self::$stylesIncluded = false;
    }
}
