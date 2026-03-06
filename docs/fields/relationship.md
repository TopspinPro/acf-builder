# Relationship

## Method

```php
$group->addRelationship(string $name, array $args = [])
```

## Example

```php
$group
    ->addRelationship('related_posts', [
        'post_type' => ['post'],
        'filters' => ['search', 'post_type', 'taxonomy'],
        'elements' => ['featured_image'],
        'min' => 0,
        'max' => 6,
    ])
    ->enableBidirectional(['field_related_posts']);
```

## Common ACF Options

- `post_type`
- `taxonomy`
- `filters`
- `elements`
- `min`
- `max`
- `return_format`
- `bidirectional`
- `bidirectional_target`

## Bidirectional Support

This is one of the main field types where the builder’s `enableBidirectional()` helper is useful.
