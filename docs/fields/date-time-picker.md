# Date Time Picker

## Method

```php
$group->addDateTimePicker(string $name, array $args = [])
```

## Example

```php
$group->addDateTimePicker('starts_at', [
    'display_format' => 'F j, Y g:i a',
    'return_format' => 'Y-m-d H:i:s',
    'first_day' => 1,
]);
```

## Common ACF Options

- `display_format`
- `return_format`
- `first_day`
