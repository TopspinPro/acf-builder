# Radio

## Method

```php
$group->addRadio(string $name, array $args = [])
```

## Example

```php
$group
    ->addRadio('layout')
    ->addChoices('grid', 'stack');
```

## Common ACF Options

- `choices`
- `default_value`
- `layout`
- `allow_null`
- `other_choice`
- `save_other_choice`
