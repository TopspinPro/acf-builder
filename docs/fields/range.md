# Range

## Method

```php
$group->addRange(string $name, array $args = [])
```

## Example

```php
$group->addRange('opacity', [
    'min' => 0,
    'max' => 100,
    'step' => 5,
    'append' => '%',
]);
```

## Common ACF Options

- `min`
- `max`
- `step`
- `prepend`
- `append`
