# Flexible Content

## Method

```php
$group->addFlexibleContent(string $name, array $args = [])
```

## Example

```php
$group
    ->addFlexibleContent('sections')
        ->addLayout('hero')
            ->addText('title')
            ->addImage('image')
        ->addLayout('gallery')
            ->addGallery('images')
    ->endFlexibleContent();
```

## Layout Methods

- `addLayout(string|FieldsBuilder $layout, array $args = [])`
- `addLayouts(array $layouts)`
- `getLayout($name)`
- `removeLayout($name)`
- `layoutExists($name)`
- `endFlexibleContent()`

## Layout Config

Each layout is a `FieldsBuilder` with these defaults:

- `name` set to the layout name
- `display` set to `block`

You can override layout config through the `$args` passed to `addLayout()`, for example:

```php
->addLayout('cta', ['label' => 'CTA', 'display' => 'row'])
```

## Field Removal and Modification

Deep paths use `->`:

```php
$group->modifyField('sections->hero->title', ['instructions' => 'Short only']);
$group->removeField('sections->gallery');
```
