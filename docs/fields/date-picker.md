# Date Picker

## Method

```php
$group->addDatePicker(string $name, array $args = [])
```

## Example

```php
$group->addDatePicker('publish_date', [
    'display_format' => 'F j, Y',
    'return_format' => 'Ymd',
    'first_day' => 1,
]);
```

## Common ACF Options

- `display_format`
- `return_format`
- `first_day`
