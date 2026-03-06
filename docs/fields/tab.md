# Tab

## Method

```php
$group->addTab(string $label, array $args = [])
```

## Example

```php
$group->addTab('Content', [
    'placement' => 'top',
    'endpoint' => 0,
]);
```

## Notes

- The label is used to generate the internal field name.
- Fields added after the tab appear under that tab until another tab is added.

## Common ACF Options

- `placement`
- `endpoint`
