# Wysiwyg

## Method

```php
$group->addWysiwyg(string $name, array $args = [])
```

## Example

```php
$group->addWysiwyg('content', [
    'tabs' => 'all',
    'toolbar' => 'full',
    'media_upload' => 1,
]);
```

## Common ACF Options

- `tabs`
- `toolbar`
- `media_upload`
- `delay`
