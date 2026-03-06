# Group

## Method

```php
$group->addGroup(string $name, array $args = [])
```

## Example

```php
$group
    ->addGroup('hero')
        ->addText('title')
        ->addImage('image')
    ->endGroup();
```

## Notes

- A group stores nested fields in `sub_fields`.
- Inside a group, regular `add...()` methods are delegated to an internal `FieldsBuilder`.
- You can call `modifyField()` and `removeField()` inside the group builder.

## Common ACF Options

- `layout`
