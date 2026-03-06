# Locations

Locations are configured from a `FieldsBuilder` with `setLocation()`.

## Basic Usage

```php
$group
    ->setLocation('post_type', '==', 'page')
        ->or('post_type', '==', 'post');
```

This builds standard ACF location groups.

## API

```php
setLocation(string $param, string $operator, string $value): LocationBuilder
```

Then chain:

- `and($param, $operator, $value)` to add another rule to the same group
- `or($param, $operator, $value)` to start a new OR group

## Example

```php
$group
    ->setLocation('post_type', '==', 'page')
        ->and('page_template', '==', 'templates/landing.php')
        ->or('options_page', '==', 'theme-settings');
```

Build result shape:

```php
[
    'location' => [
        [
            ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
            ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/landing.php'],
        ],
        [
            ['param' => 'options_page', 'operator' => '==', 'value' => 'theme-settings'],
        ],
    ],
]
```

## Notes

- Location params and values are passed through directly to ACF.
- This library does not validate allowed location rule names.
- If you call `setLocation()` from a nested builder, it delegates back to the root field group.
