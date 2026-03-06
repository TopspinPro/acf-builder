# Taxonomy

## Method

```php
$group->addTaxonomy(string $name, array $args = [])
```

## Example

```php
$group->addTaxonomy('categories', [
    'taxonomy' => 'category',
    'field_type' => 'multi_select',
    'return_format' => 'id',
    'add_term' => 0,
]);
```

## Common ACF Options

- `taxonomy`
- `field_type`
- `allow_null`
- `load_terms`
- `save_terms`
- `add_term`
- `return_format`
- `multiple`
- `bidirectional`
- `bidirectional_target`
