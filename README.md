# ACF Builder

Create configuration arrays for [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/) using the builder pattern and a fluent API.

This package is an internal fork of the abandoned `stoutlogic/acf-builder` library, rebuilt to stay close to the original API while supporting PHP 8.4 and current ACF bidirectional field settings.

## Install

```bash
composer require tsp/acf-builder
```

If your project is not using Composer, require `autoload.php`.

## Simple Example

```php
$banner = new Tsp\AcfBuilder\FieldsBuilder('banner');
$banner
    ->addText('title')
    ->addWysiwyg('content')
    ->addImage('background_image')
    ->setLocation('post_type', '==', 'page')
        ->or('post_type', '==', 'post');

add_action('acf/init', function () use ($banner) {
    acf_add_local_field_group($banner->build());
});
```

`$banner->build()` returns:

```php
[
    'key' => 'group_banner',
    'title' => 'Banner',
    'fields' => [
        [
            'key' => 'field_title',
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
        ],
        [
            'key' => 'field_content',
            'name' => 'content',
            'label' => 'Content',
            'type' => 'wysiwyg',
        ],
        [
            'key' => 'field_background_image',
            'name' => 'background_image',
            'label' => 'Background Image',
            'type' => 'image',
        ],
    ],
    'location' => [
        [
            [
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'page',
            ],
        ],
        [
            [
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'post',
            ],
        ],
    ],
];
```

## Reuse Example

```php
use Tsp\AcfBuilder\FieldsBuilder;

$background = new FieldsBuilder('background');
$background
    ->addTab('Background')
    ->addImage('background_image')
    ->addTrueFalse('fixed')
        ->instructions("Check to add a parallax effect where the background image doesn't move when scrolling")
    ->addColorPicker('background_color');

$banner = new FieldsBuilder('banner');
$banner
    ->addTab('Content')
    ->addText('title')
    ->addWysiwyg('content')
    ->addFields($background)
    ->setLocation('post_type', '==', 'page');
```

## Bidirectional Fields

ACF bidirectional support is exposed as one small helper on `FieldBuilder`:

```php
$people = new Tsp\AcfBuilder\FieldsBuilder('people');
$people
    ->addRelationship('related_people')
    ->enableBidirectional(['field_person_relations']);
```

That produces the native ACF keys:

```php
[
    'bidirectional' => 1,
    'bidirectional_target' => ['field_person_relations'],
]
```

The helper only emits ACF config. Supported field types and target compatibility remain ACF rules.

## Runtime Metadata Helpers

Some projects layer admin runtime behavior on top of the built ACF arrays. This package exposes small helpers for those integrations while still only returning configuration arrays.

```php
use Tsp\AcfBuilder\DependentChoices;
use Tsp\AcfBuilder\FieldsBuilder;

$course = new FieldsBuilder('course');
$course
    ->enableEditorSwitcher('Settings', 20)
    ->resetTabsOnSave()
    ->addUser('course_author')
    ->addSelect('course_author_profile')
        ->dependentChoices(
            DependentChoices::select()
                ->controlledBy('course_author')
                ->controllerValues([AuthorProfiles::class, 'getAuthorIds'])
                ->choices([AuthorProfiles::class, 'getChoicesForAuthor'])
                ->sanitizeControllerAsUserId()
                ->sanitizeValueAsKey()
                ->clearWhenControllerEmpty()
        )
        ->adminVisibleIf([
            'field_name' => 'course_author',
            'operator' => '!=',
            'value' => '',
        ]);
```

The builder does not enqueue scripts or register WordPress hooks. Runtime code remains responsible for consuming custom metadata such as `dependent_choices`, `editor_switcher`, `reset_tabs_on_save`, `admin_visibility`, `min_date`, `max_date`, and `linked_date_field`.

## Tests

```bash
vendor/bin/phpunit
```

## Requirements

- PHP `^8.4`
- `doctrine/inflector` `^2.0`
