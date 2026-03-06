# Repeater

## Method

```php
$group->addRepeater(string $name, array $args = [])
```

## Example

```php
$group
    ->addRepeater('slides', [
        'min' => 1,
        'layout' => 'block',
    ])
        ->addText('title')
        ->addImage('image')
    ->endRepeater();
```

## Common ACF Options

- `min`
- `max`
- `layout`
- `button_label`
- `collapsed`

## Notes

- If `button_label` is omitted, the builder generates one automatically.
- `collapsed` should reference a sub field name. The builder resolves that to the correct field key at build time.
