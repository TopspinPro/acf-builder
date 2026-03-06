# Nested Builders

Some fields open nested builder contexts.

## Group

```php
$group
    ->addGroup('hero')
        ->addText('title')
        ->addImage('image')
    ->endGroup();
```

The nested fields become `sub_fields`.

## Repeater

```php
$group
    ->addRepeater('slides')
        ->addText('title')
        ->addImage('image')
    ->endRepeater();
```

Fields added inside the repeater become `sub_fields`.

## Flexible Content

```php
$group
    ->addFlexibleContent('sections')
        ->addLayout('hero')
            ->addText('title')
        ->addLayout('gallery')
            ->addGallery('images')
    ->endFlexibleContent();
```

Each layout is internally a `FieldsBuilder`.

## Deep Field Modification

Use `->` to target nested fields:

```php
$group->modifyField('hero->image', ['instructions' => 'Desktop crop only']);
$group->removeField('sections->hero->title');
```

This works for nested `group`, `repeater`, and `flexible_content` structures.
