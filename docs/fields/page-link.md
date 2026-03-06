# Page Link

## Method

```php
$group->addPageLink(string $name, array $args = [])
```

## Example

```php
$group->addPageLink('target_page', [
    'post_type' => ['page', 'post'],
    'allow_archives' => 1,
    'multiple' => 0,
]);
```

## Common ACF Options

- `post_type`
- `taxonomy`
- `allow_null`
- `multiple`
- `allow_archives`
