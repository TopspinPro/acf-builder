# Post Object

## Method

```php
$group->addPostObject(string $name, array $args = [])
```

## Example

```php
$group
    ->addPostObject('featured_post', [
        'post_type' => ['post'],
        'return_format' => 'object',
        'ui' => 1,
    ]);
```

## Common ACF Options

- `post_type`
- `taxonomy`
- `allow_null`
- `multiple`
- `return_format`
- `ui`
- `bidirectional`
- `bidirectional_target`
