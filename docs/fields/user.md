# User

## Method

```php
$group->addUser(string $name, array $args = [])
```

## Example

```php
$group->addUser('owner', [
    'role' => ['administrator', 'editor'],
    'allow_null' => 1,
    'multiple' => 0,
    'return_format' => 'array',
]);
```

## Common ACF Options

- `role`
- `allow_null`
- `multiple`
- `return_format`
- `bidirectional`
- `bidirectional_target`
