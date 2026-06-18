<?php

namespace Tsp\AcfBuilder\Runtime;

class DatePickerFields
{
    private static ?AdminAssets $assets = null;
    private static bool $hasRegisteredHooks = false;

    public static function register(AdminAssets $assets): void
    {
        self::$assets = $assets;

        if (self::$hasRegisteredHooks) {
            return;
        }

        add_filter('acf/prepare_field', [self::class, '_prepareField']);
        add_action('acf/input/admin_enqueue_scripts', [self::class, '_enqueueAdminAssets']);

        self::$hasRegisteredHooks = true;
    }

    public static function _prepareField(mixed $field): mixed
    {
        if (!is_array($field) || ($field['type'] ?? '') !== 'date_picker') {
            return $field;
        }

        if (empty($field['min_date']) && empty($field['max_date']) && empty($field['linked_date_field'])) {
            return $field;
        }

        $field['wrapper']['data-min-date'] = $field['min_date'] ?? '';
        $field['wrapper']['data-max-date'] = $field['max_date'] ?? '';
        $field['wrapper']['data-linked-field'] = $field['linked_date_field'] ?? '';

        return $field;
    }

    public static function _enqueueAdminAssets(): void
    {
        self::$assets?->enqueue();
    }
}
