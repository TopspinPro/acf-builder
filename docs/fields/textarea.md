# Textarea

## Method

```php
$group->addTextarea(string $name, array $args = [])
```

## Example

```php
$group->addTextarea('summary', [
    'rows' => 4,
    'new_lines' => 'wpautop',
]);
```

## Common ACF Options

- `rows`
- `new_lines`
- `maxlength`
- `placeholder`
