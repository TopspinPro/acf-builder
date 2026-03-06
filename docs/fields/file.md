# File

## Method

```php
$group->addFile(string $name, array $args = [])
```

## Example

```php
$group->addFile('download', [
    'return_format' => 'array',
    'library' => 'uploadedTo',
    'mime_types' => 'pdf,zip',
]);
```

## Common ACF Options

- `return_format`
- `library`
- `min_size`
- `max_size`
- `mime_types`
