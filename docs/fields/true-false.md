# True / False

## Method

```php
$group->addTrueFalse(string $name, array $args = [])
```

## Example

```php
$group->addTrueFalse('featured', [
    'ui' => 1,
    'message' => 'Highlight this item',
    'default_value' => 0,
]);
```

## Common ACF Options

- `ui`
- `message`
- `default_value`
- `ui_on_text`
- `ui_off_text`
