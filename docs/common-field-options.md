# Common Field Options

Every field builder accepts an `$args` array when the field is created. Those values are merged directly into the final ACF field config.

## Standard Pattern

```php
$group->addText('headline', [
    'instructions' => 'Shown above the hero image',
    'required' => 1,
    'placeholder' => 'Enter a headline',
]);
```

## Common Builder Methods

These methods are available on `FieldBuilder` and on most field-specific builders returned by `add...()` calls.

- `setConfig($key, $value)`
- `updateConfig(array $config)`
- `setKey($key)`
- `setCustomKey($key)`
- `hasCustomKey()`
- `setRequired()`
- `setUnrequired()`
- `setLabel($label)`
- `setInstructions($instructions)`
- `setDefaultValue($value)`
- `conditional($fieldNameOrKey, $operator, $value)`
- `setWrapper(array $config)`
- `getWrapper()`
- `setWidth($width)`
- `setAttr($name, $value = null)`
- `setSelector($selector)`
- `enableBidirectional(string|array $targets)`

## Wrapper Helpers

```php
$group
    ->addText('cta_label')
    ->setWidth('50%')
    ->setAttr('class', 'field--cta')
    ->setSelector('#cta-label.field--half');
```

This writes to the ACF `wrapper` key.

## Conditional Logic

```php
$group
    ->addTrueFalse('show_cta')
    ->addText('cta_label')
        ->conditional('show_cta', '==', '1');
```

Use the field name in most cases. Custom field keys also work.

## Bidirectional Helper

```php
$group
    ->addRelationship('related_posts')
    ->enableBidirectional(['field_related_posts']);
```

This sets:

```php
[
    'bidirectional' => 1,
    'bidirectional_target' => ['field_related_posts'],
]
```

The library does not enforce which ACF field types support bidirectional relationships. ACF remains the source of truth.
