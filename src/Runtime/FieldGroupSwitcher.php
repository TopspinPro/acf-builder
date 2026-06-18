<?php

namespace Tsp\AcfBuilder\Runtime;

use Tsp\AcfBuilder\AdminVisibility;
use Tsp\AcfBuilder\Runtime\Support\RuntimeUtils;

class FieldGroupSwitcher
{
    private const FIELD_GROUP_KEYS = ['editor_switcher', 'admin_visibility'];

    private static ?AdminAssets $assets = null;
    private static array $configs = [];
    private static array $options = [];
    private static bool $hasRegisteredHooks = false;
    private static bool $hasRegisteredEnqueueHook = false;

    public static function register(AdminAssets $assets, array $options = []): void
    {
        self::$assets = $assets;
        self::$options = array_replace([
            'ariaLabel' => 'ACF field group editor view',
            'editLabel' => 'Edit',
            'allFieldsLabel' => 'All fields',
            'storagePrefix' => 'tsp_acf_field_group_switcher',
        ], $options);

        if (self::$hasRegisteredHooks) {
            return;
        }

        add_filter('tsp/acf/field_group_config', [self::class, '_extractConfigsFromFieldGroup'], 10, 3);

        self::$hasRegisteredHooks = true;
    }

    public static function _extractConfigsFromFieldGroup(array $fieldGroupConfig, string $fieldGroupClass = '', mixed $fieldGroupInstance = null): array
    {
        unset($fieldGroupClass, $fieldGroupInstance);

        $switcherConfig = self::normalizeEditorSwitcherConfig($fieldGroupConfig['editor_switcher'] ?? []);
        $switcherEnabled = ($switcherConfig['enabled'] ?? false) === true;
        $visibilityRule = AdminVisibility::normalize($fieldGroupConfig['admin_visibility'] ?? null);
        $hasFieldVisibilityRules = self::hasFieldVisibilityRules((array) ($fieldGroupConfig['fields'] ?? []));
        $groupKey = RuntimeUtils::sanitizeKey((string) ($fieldGroupConfig['key'] ?? ''));
        $fieldGroupConfig = self::stripRuntimeConfig($fieldGroupConfig);

        if ($groupKey === '') {
            return $fieldGroupConfig;
        }

        if (!$switcherEnabled && $visibilityRule === null && !$hasFieldVisibilityRules) {
            return $fieldGroupConfig;
        }

        $label = RuntimeUtils::sanitizeText((string) ($switcherConfig['label'] ?? $fieldGroupConfig['title'] ?? $groupKey));

        self::registerConfig([
            'key' => $groupKey,
            'label' => $label !== '' ? $label : $groupKey,
            'order' => self::normalizeOrder($switcherConfig['order'] ?? null),
            'switcherEnabled' => $switcherEnabled,
            'visibleIf' => $visibilityRule,
        ]);

        return $fieldGroupConfig;
    }

    public static function _enqueueAdminAssets(): void
    {
        if (self::$assets === null) {
            return;
        }

        $groups = self::getClientConfigs();
        if ($groups === []) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        self::$assets->enqueue();
        self::$assets->localize('tsp_acf_field_group_switcher', [
            'ariaLabel' => self::$options['ariaLabel'],
            'editLabel' => self::$options['editLabel'],
            'allFieldsLabel' => self::$options['allFieldsLabel'],
            'screenId' => RuntimeUtils::sanitizeKey((string) ($screen->id ?? 'acf')),
            'storagePrefix' => self::$options['storagePrefix'],
            'groups' => $groups,
        ]);
    }

    private static function registerConfig(array $config): void
    {
        $groupKey = RuntimeUtils::sanitizeKey((string) ($config['key'] ?? ''));
        if ($groupKey === '') {
            return;
        }

        self::$configs[$groupKey] = $config;

        if (self::$hasRegisteredEnqueueHook) {
            return;
        }

        add_action('acf/input/admin_enqueue_scripts', [self::class, '_enqueueAdminAssets']);

        self::$hasRegisteredEnqueueHook = true;
    }

    private static function getClientConfigs(): array
    {
        $configs = self::$configs;

        uasort($configs, [self::class, 'compareConfigs']);

        return array_values(array_map(
            fn (array $config): array => array_filter([
                'key' => $config['key'],
                'label' => $config['label'],
                'order' => $config['order'],
                'switcherEnabled' => $config['switcherEnabled'] ?? false,
                'visibleIf' => $config['visibleIf'] ?? null,
            ], static fn (mixed $value): bool => $value !== null && $value !== []),
            $configs
        ));
    }

    private static function compareConfigs(array $left, array $right): int
    {
        $orderComparison = $right['order'] <=> $left['order'];
        if ($orderComparison !== 0) {
            return $orderComparison;
        }

        $labelComparison = strcasecmp((string) $left['label'], (string) $right['label']);
        if ($labelComparison !== 0) {
            return $labelComparison;
        }

        return strcmp((string) $left['key'], (string) $right['key']);
    }

    private static function normalizeOrder(mixed $order): int
    {
        return is_numeric($order) ? (int) $order : 10;
    }

    private static function normalizeEditorSwitcherConfig(mixed $config): array
    {
        if (!is_array($config)) {
            return [];
        }

        return array_filter([
            'enabled' => ($config['enabled'] ?? false) === true,
            'label' => isset($config['label']) ? RuntimeUtils::sanitizeText($config['label']) : null,
            'order' => isset($config['order']) && is_numeric($config['order']) ? (int) $config['order'] : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private static function stripRuntimeConfig(array $fieldGroupConfig): array
    {
        foreach (self::FIELD_GROUP_KEYS as $fieldGroupKey) {
            unset($fieldGroupConfig[$fieldGroupKey]);
        }

        return $fieldGroupConfig;
    }

    private static function hasFieldVisibilityRules(array $fields): bool
    {
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            if (isset($field['wrapper'][AdminVisibility::WRAPPER_ATTRIBUTE])) {
                return true;
            }

            foreach (['fields', 'sub_fields'] as $nestedFieldKey) {
                if (is_array($field[$nestedFieldKey] ?? null) && self::hasFieldVisibilityRules($field[$nestedFieldKey])) {
                    return true;
                }
            }

            if (!is_array($field['layouts'] ?? null)) {
                continue;
            }

            foreach ($field['layouts'] as $layout) {
                if (is_array($layout['sub_fields'] ?? null) && self::hasFieldVisibilityRules($layout['sub_fields'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
