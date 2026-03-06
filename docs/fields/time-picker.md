# Time Picker

## Method

```php
$group->addTimePicker(string $name, array $args = [])
```

## Example

```php
$group->addTimePicker('start_time', [
    'display_format' => 'g:i a',
    'return_format' => 'H:i:s',
]);
```

## Common ACF Options

- `display_format`
- `return_format`
