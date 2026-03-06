# Gallery

## Method

```php
$group->addGallery(string $name, array $args = [])
```

## Example

```php
$group->addGallery('images', [
    'preview_size' => 'thumbnail',
    'insert' => 'append',
    'min' => 1,
    'max' => 12,
]);
```

## Common ACF Options

- `preview_size`
- `insert`
- `library`
- `min`
- `max`
- `mime_types`
