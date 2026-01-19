# Templar

A lightweight, powerful PHP templating engine with a clean, distinctive syntax.

## Installation

```bash
composer require pauldeano/templar
```

## Quick Start

```php
<?php
require 'vendor/autoload.php';

use Templar\Templar;

$templar = new Templar([
    'view_path' => './views'
]);

echo $templar->render('home', [
    'name' => 'Paul',
    'user' => ['email' => 'paul@example.com']
]);
```

## Syntax

### Variable Output: `% variable %`

Use percent signs with spaces for escaped output:

```html
<h1>Hello, % name %</h1>
<p>Email: % user.email %</p>
<p>First item: % items.0 %</p>
```

**Features:**
- Auto-escapes HTML (XSS safe)
- Supports dot notation for arrays and objects
- Supports numeric indexes: `% items.0.title %`

### Intelligent Dump: `%! variable !%`

Use `%!` and `!%` for intelligent variable inspection:

```html
<!-- Debug a variable -->
%! user !%

<!-- Debug nested data -->
%! request.params !%
```

**Output varies by type:**
| Type | Output |
|------|--------|
| String | Raw string |
| Number | Formatted number |
| Boolean | `true` / `false` |
| Array | Expandable formatted dump |
| Object | Class name + properties |
| Closure | Signature + file location |
| null | `null` |

### Directives: `@directive()`

Control flow and logic use `@` directives:

```html
@if(user.admin)
    <span class="badge">Admin</span>
@endif

@foreach(items as item)
    <li>% item.name %</li>
@endforeach

@while(condition)
    <!-- content -->
@endwhile
```

**Available directives:**
- `@if(condition)` / `@else` / `@endif`
- `@foreach(array as item)` / `@endforeach`
- `@while(condition)` / `@endwhile`
- `@do` / `@dowhile(condition)`
- `@dump(variable)` - inline var_dump
- `@uppercase(string)` - uppercase output
- `@dateformat(date)` - format as Y-m-d

## Examples

### Basic Template

```html
<!-- views/profile.php -->
<div class="profile">
    <h1>% user.name %</h1>
    <p>% user.bio %</p>

    @if(user.posts)
        <h2>Posts</h2>
        <ul>
        @foreach(user.posts as post)
            <li>% post.title % - @dateformat(post.created_at)</li>
        @endforeach
        </ul>
    @endif
</div>
```

### Debugging with `%! !%`

```html
<!-- Quick debug during development -->
<div class="debug">
    <h3>User Object:</h3>
    %! user !%

    <h3>All Data:</h3>
    %! __data__ !%
</div>
```

### Using in PHP

```php
$templar = new Templar();

// Render a view file
echo $templar->render('profile', ['user' => $user]);

// Render a string template
echo $templar->renderString('<h1>% title %</h1>', ['title' => 'Hello']);

// Register custom directive
$templar->registerDirective('money', function($expr) {
    return "<?php echo '$' . number_format($expr, 2); ?>";
});
```

### Include Dump Styles (Optional)

The dump output is auto-styled, but you can manually include styles:

```php
// In your layout <head>
echo Templar::dumpStyles();
```

## Custom Directives

Register your own directives:

```php
$templar->boot(function($t) {
    // @money(amount) - format as currency
    $t->registerDirective('money', function($expr) {
        return "<?php echo '£' . number_format({$expr}, 2); ?>";
    });

    // @json(data) - output as JSON
    $t->registerDirective('json', function($expr) {
        return "<?php echo json_encode({$expr}); ?>";
    });

    // @truncate(text, 100) - truncate text
    $t->registerDirective('truncate', function($expr) {
        return "<?php
            \$parts = explode(',', '{$expr}');
            \$text = trim(\$parts[0]);
            \$len = isset(\$parts[1]) ? (int)trim(\$parts[1]) : 100;
            echo strlen(\$text) > \$len ? substr(\$text, 0, \$len) . '...' : \$text;
        ?>";
    });
});
```

## Configuration

```php
$templar = new Templar([
    'view_path' => './resources/views',      // Where templates live
    'cache_path' => './storage/cache',       // Compiled template cache
    'components_path' => './views/components' // Component templates
]);
```

## Why Templar?

- **Simple syntax**: `% var %` is faster to type than `{{ $var }}`
- **Intelligent dumps**: Debug any variable type beautifully
- **Standalone**: No framework dependencies
- **Extensible**: Add your own directives easily
- **Lightweight**: Minimal footprint, maximum power

## License

MIT
