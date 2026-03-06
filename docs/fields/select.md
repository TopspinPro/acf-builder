# Select

## Method

```php
$group->addSelect(string $name, array $args = [])
```

## Example

```php
$group
    ->addSelect('theme')
    ->addChoices([
        'light' => 'Light',
        'dark' => 'Dark',
    ]);
```

## Choice Builder Methods

- `addChoice($value, $label = null)`
- `addChoices(array ...$choices)`
- `setChoices(array ...$choices)`

## Common ACF Options

- `choices`
- `default_value`
- `allow_null`
- `multiple`
- `ui`
- `ajax`
- `return_format`
- `placeholder`
