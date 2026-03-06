# Button Group

## Method

```php
$group->addButtonGroup(string $name, array $args = [])
```

## Example

```php
$group
    ->addButtonGroup('alignment')
    ->addChoices([
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
    ]);
```

## Common ACF Options

- `choices`
- `default_value`
- `layout`
