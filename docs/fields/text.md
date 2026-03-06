# Text

## Method

```php
$group->addText(string $name, array $args = [])
```

## Example

```php
$group->addText('headline', [
    'placeholder' => 'Enter headline',
    'maxlength' => 120,
]);
```

## Common ACF Options

- `placeholder`
- `maxlength`
- `prepend`
- `append`
- `readonly`
- `disabled`

## Builder Options

- Any option from [Common Field Options](../common-field-options.md)
