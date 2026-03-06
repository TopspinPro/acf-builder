# Field Groups

Field groups are built with `Tsp\AcfBuilder\FieldsBuilder`.

## Basic Usage

```php
use Tsp\AcfBuilder\FieldsBuilder;

$banner = new FieldsBuilder('banner');

$banner
    ->addText('title')
    ->addImage('background_image')
    ->setLocation('post_type', '==', 'page');

$config = $banner->build();
```

## Constructor

```php
new FieldsBuilder(string $name, array $groupConfig = [])
```

- `$name` becomes the base for the group title and key.
- `$groupConfig` is merged directly into the field group config.

## Group Methods

- `setGroupConfig($key, $value)` sets one group-level config value.
- `getGroupConfig($key)` reads one group-level config value.
- `updateGroupConfig(array $config)` merges many group-level config values.
- `addFields(FieldsBuilder|array $fields)` reuses fields from another builder or raw array.
- `modifyField(string $name, array|\Closure $modify)` updates a field after adding it.
- `removeField(string $name)` removes a field by name.
- `setLocation(string $param, string $operator, string $value)` starts location rules.
- `build()` returns the final array for `acf_add_local_field_group()`.

## Group Config Examples

```php
$group = new FieldsBuilder('seo', [
    'position' => 'acf_after_title',
    'style' => 'default',
    'menu_order' => 5,
]);
```

Typical ACF group config keys you can pass through:

- `key`
- `title`
- `position`
- `style`
- `menu_order`
- `hide_on_screen`
- `active`
- `description`

## Reusing Fields

```php
$background = new FieldsBuilder('background');
$background
    ->addImage('image')
    ->addColorPicker('color');

$hero = new FieldsBuilder('hero');
$hero
    ->addText('title')
    ->addFields($background);
```

When reusing another `FieldsBuilder`, the builder is cloned before insertion.
