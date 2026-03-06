# Checkbox

## Method

```php
$group->addCheckbox(string $name, array $args = [])
```

## Example

```php
$group
    ->addCheckbox('features', [
        'layout' => 'vertical',
        'toggle' => 1,
    ])
    ->addChoices([
        'search' => 'Search',
        'filter' => 'Filter',
    ]);
```

## Common ACF Options

- `choices`
- `default_value`
- `layout`
- `toggle`
- `return_format`
