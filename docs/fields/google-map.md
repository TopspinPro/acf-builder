# Google Map

## Method

```php
$group->addGoogleMap(string $name, array $args = [])
```

## Example

```php
$group->addGoogleMap('location', [
    'center_lat' => '51.5074',
    'center_lng' => '-0.1278',
    'zoom' => 12,
    'height' => 400,
]);
```

## Common ACF Options

- `center_lat`
- `center_lng`
- `zoom`
- `height`
