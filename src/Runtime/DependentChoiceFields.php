<?php

namespace Tsp\AcfBuilder\Runtime;

use Tsp\AcfBuilder\Runtime\Support\DependentChoiceUtils;
use Tsp\AcfBuilder\Runtime\Support\RuntimeUtils;

class DependentChoiceFields
{
    private static ?AdminAssets $assets = null;
    private static array $configs = [];
    private static array $registeredTargetFieldNames = [];
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

        $extracted = DependentChoiceUtils::extractFieldGroupConfig($fieldGroupConfig);

        foreach ($extracted['configs'] as $config) {
            self::registerConfig($config);
        }

        return $extracted['field_group_config'];
    }

    public static function _populateFieldChoices(array $field): array
    {
        $config = self::getConfigForField($field['name'] ?? '');

        if ($config === null) {
            return $field;
        }

        $controllerValue = DependentChoiceUtils::resolveSubmittedControllerValue($config);
        $field['choices'] = DependentChoiceUtils::resolveChoices($config, $controllerValue);

        return $field;
    }

    public static function _validateFieldValue(mixed $valid, mixed $value, array $field, mixed $input): mixed
    {
        unset($input);

        if ($valid !== true) {
            return $valid;
        }

        $config = self::getConfigForField($field['name'] ?? '');

        if ($config === null) {
            return $valid;
        }

        $controllerValue = DependentChoiceUtils::resolveSubmittedControllerValue($config);
        if (DependentChoiceUtils::shouldClearTargetForControllerValue($config, $controllerValue)) {
            return true;
        }

        $allowedValues = DependentChoiceUtils::getAllowedChoiceValues(
            DependentChoiceUtils::resolveChoices($config, $controllerValue)
        );
        $submittedValues = DependentChoiceUtils::normalizeSubmittedValues($value, $config);

        if ($config['field_type'] === 'checkbox') {
            return DependentChoiceUtils::validateCheckboxValues($config, $submittedValues, $allowedValues);
        }

        return DependentChoiceUtils::validateScalarValue($config, $submittedValues[0] ?? '', $allowedValues);
    }

    public static function _normalizeFieldValue(mixed $value, mixed $postId, array $field): mixed
    {
        unset($postId);

        $config = self::getConfigForField($field['name'] ?? '');

        if ($config === null) {
            return $value;
        }

        $controllerValue = DependentChoiceUtils::resolveSubmittedControllerValue($config);
        if (DependentChoiceUtils::shouldClearTargetForControllerValue($config, $controllerValue)) {
            return $config['field_type'] === 'checkbox' ? [] : '';
        }

        $allowedValues = DependentChoiceUtils::getAllowedChoiceValues(
            DependentChoiceUtils::resolveChoices($config, $controllerValue)
        );
        $submittedValues = DependentChoiceUtils::normalizeSubmittedValues($value, $config);

        if ($config['field_type'] === 'checkbox') {
            return array_values(array_intersect($submittedValues, $allowedValues));
        }

        return in_array($submittedValues[0] ?? '', $allowedValues, true)
            ? ($submittedValues[0] ?? '')
            : '';
    }

    public static function _enqueueAdminAssets(string $hook): void
    {
        if (self::$assets === null) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $clientConfigs = DependentChoiceUtils::getClientConfigsForScreen(self::$configs, $hook, $screen);

        if ($clientConfigs === []) {
            return;
        }

        self::$assets->enqueue();
        self::$assets->localize('tsp_acf_dependent_choice_fields', $clientConfigs);
    }

    private static function registerConfig(array $config): void
    {
        $targetFieldName = RuntimeUtils::sanitizeKey((string) ($config['target_field_name'] ?? ''));

        if ($targetFieldName === '') {
            return;
        }

        self::$configs[$targetFieldName] = $config;

        if (!in_array($targetFieldName, self::$registeredTargetFieldNames, true)) {
            add_filter('acf/load_field/name=' . $targetFieldName, [self::class, '_populateFieldChoices']);
            add_filter('acf/validate_value/name=' . $targetFieldName, [self::class, '_validateFieldValue'], 10, 4);
            add_filter('acf/update_value/name=' . $targetFieldName, [self::class, '_normalizeFieldValue'], 10, 3);

            self::$registeredTargetFieldNames[] = $targetFieldName;
        }

        if (self::$hasRegisteredEnqueueHook) {
            return;
        }

        add_action('admin_enqueue_scripts', [self::class, '_enqueueAdminAssets']);

        self::$hasRegisteredEnqueueHook = true;
    }

    private static function getConfigForField(string $fieldName): ?array
    {
        $fieldName = RuntimeUtils::sanitizeKey($fieldName);

        if ($fieldName === '') {
            return null;
        }

        return self::$configs[$fieldName] ?? null;
    }
}
