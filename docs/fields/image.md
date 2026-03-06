# Image

## Method

```php
$group->addImage(string $name, array $args = [])
```

## Example

```php
$group->addImage('hero_image', [
    'return_format' => 'array',
    'preview_size' => 'medium',
    'library' => 'all',
]);
```

## Common ACF Options

- `return_format`
- `preview_size`
- `library`
- `min_width`
- `min_height`
- `max_width`
- `max_height`
- `mime_types`
