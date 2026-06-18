<?php

namespace Tsp\AcfBuilder\Runtime;

use Tsp\AcfBuilder\Runtime\Support\RuntimeUtils;

class TabPreferences
{
    private static ?AdminAssets $assets = null;
    private static array $configs = [];
    private static bool $hasRegisteredHooks = false;
    private static bool $hasRegisteredEnqueueHook = false;

    public static function register(AdminAssets $assets): void
    {
        self::$assets = $assets;

        if (self::$hasRegisteredHooks) {
            return;
        }

        add_filter('tsp/acf/field_group_config', [self::class, '_extractConfigsFromFieldGroup'], 10, 3);

        self::$hasRegisteredHooks = true;
    }

    public static function _extractConfigsFromFieldGroup(array $fieldGroupConfig, string $fieldGroupClass = '', mixed $fieldGroupInstance = null): array
    {
        unset($fieldGroupClass, $fieldGroupInstance);

        $resetTabsOnSave = !empty($fieldGroupConfig['reset_tabs_on_save']);
        unset($fieldGroupConfig['reset_tabs_on_save']);

        if (!$resetTabsOnSave) {
            return $fieldGroupConfig;
        }

        $groupKey = RuntimeUtils::sanitizeKey((string) ($fieldGroupConfig['key'] ?? ''));
        if ($groupKey === '') {
            return $fieldGroupConfig;
        }

        self::registerConfig([
            'key' => $groupKey,
            'location' => $fieldGroupConfig['location'] ?? [],
        ]);

        return $fieldGroupConfig;
    }

    public static function _enqueueAdminAssets(string $hook): void
    {
        if (self::$assets === null) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $clientConfigs = self::getClientConfigsForScreen($hook, $screen);

        if ($clientConfigs === []) {
            return;
        }

        self::$assets->enqueue();
        self::$assets->localize('tsp_acf_tab_preferences', [
            'groups' => $clientConfigs,
        ]);
    }

    public static function fieldGroupMatchesScreen(array $config, mixed $screen, ?string $optionsPageSlug = null): bool
    {
        $locationGroups = $config['location'] ?? [];

        if ($locationGroups === [] || !is_array($locationGroups)) {
            return true;
        }

        foreach ($locationGroups as $locationGroup) {
            if (is_array($locationGroup) && self::locationGroupMatchesScreen($locationGroup, $screen, $optionsPageSlug)) {
                return true;
            }
        }

        return false;
    }

    public static function fieldGroupMatchesOptionsPage(array $config, mixed $screen, ?string $optionsPageSlug): bool
    {
        if ($optionsPageSlug === null || !self::fieldGroupHasLocationParam($config, 'options_page')) {
            return false;
        }

        return self::fieldGroupMatchesScreen($config, $screen, $optionsPageSlug);
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

        add_action('admin_enqueue_scripts', [self::class, '_enqueueAdminAssets']);

        self::$hasRegisteredEnqueueHook = true;
    }

    private static function getClientConfigsForScreen(string $hook, mixed $screen): array
    {
        $isPostScreen = in_array($hook, ['post.php', 'post-new.php'], true);
        $optionsPageSlug = RuntimeUtils::currentOptionsPageSlug();

        if (!$isPostScreen && $optionsPageSlug === null) {
            return [];
        }

        return array_values(array_map(
            fn (array $config): array => [
                'key' => $config['key'],
            ],
            array_filter(
                self::$configs,
                fn (array $config): bool => $isPostScreen
                    ? self::fieldGroupMatchesScreen($config, $screen)
                    : self::fieldGroupMatchesOptionsPage($config, $screen, $optionsPageSlug)
            )
        ));
    }

    private static function locationGroupMatchesScreen(array $locationGroup, mixed $screen, ?string $optionsPageSlug = null): bool
    {
        foreach ($locationGroup as $rule) {
            if (!is_array($rule) || !self::locationRuleMatchesScreen($rule, $screen, $optionsPageSlug)) {
                return false;
            }
        }

        return true;
    }

    private static function locationRuleMatchesScreen(array $rule, mixed $screen, ?string $optionsPageSlug = null): bool
    {
        $operator = (string) ($rule['operator'] ?? '==');
        $expected = (string) ($rule['value'] ?? '');

        $actual = match ($rule['param'] ?? '') {
            'post_type' => (string) ($screen->post_type ?? ''),
            'options_page' => $optionsPageSlug,
            default => null,
        };

        if ($actual === null) {
            return true;
        }

        return match ($operator) {
            '!=' => $actual !== $expected,
            default => $actual === $expected,
        };
    }

    private static function fieldGroupHasLocationParam(array $config, string $param): bool
    {
        $locationGroups = $config['location'] ?? [];

        if (!is_array($locationGroups)) {
            return false;
        }

        foreach ($locationGroups as $locationGroup) {
            if (!is_array($locationGroup)) {
                continue;
            }

            foreach ($locationGroup as $rule) {
                if (is_array($rule) && ($rule['param'] ?? '') === $param) {
                    return true;
                }
            }
        }

        return false;
    }
}
