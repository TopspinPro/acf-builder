<?php

namespace Tsp\AcfBuilder\Runtime\Support;

class DependentChoiceUtils
{
    public static function normalizeConfig(array $config): ?array
    {
        $targetFieldName = RuntimeUtils::sanitizeKey((string) ($config['target_field_name'] ?? ''));
        $controllerFieldName = RuntimeUtils::sanitizeKey((string) ($config['controller_field_name'] ?? ''));
        $fieldType = RuntimeUtils::sanitizeKey((string) ($config['field_type'] ?? 'checkbox'));
        $choicesResolver = $config['choices_resolver'] ?? null;

        if ($targetFieldName === '' || $controllerFieldName === '' || !in_array($fieldType, ['checkbox', 'select'], true) || !is_callable($choicesResolver)) {
            return null;
        }

        $valueSanitizer = $config['value_sanitizer'] ?? static fn (mixed $rawValue): string => RuntimeUtils::sanitizeText((string) $rawValue);

        if (!is_callable($valueSanitizer)) {
            return null;
        }

        $controllerValueSanitizer = $config['controller_value_sanitizer'] ?? $valueSanitizer;

        if (!is_callable($controllerValueSanitizer)) {
            return null;
        }

        $controllerValuesProvider = $config['controller_values_provider'] ?? null;

        if ($controllerValuesProvider !== null && !is_callable($controllerValuesProvider)) {
            return null;
        }

        $screenMatcher = $config['screen_matcher'] ?? null;

        if ($screenMatcher !== null && !is_callable($screenMatcher)) {
            return null;
        }

        return [
            'target_field_name' => $targetFieldName,
            'controller_field_name' => $controllerFieldName,
            'field_type' => $fieldType,
            'choices_resolver' => $choicesResolver,
            'controller_values_provider' => $controllerValuesProvider,
            'screen_matcher' => $screenMatcher,
            'invalid_value_message' => RuntimeUtils::sanitizeText((string) ($config['invalid_value_message'] ?? 'Please select only valid values for this field.')),
            'value_sanitizer' => $valueSanitizer,
            'controller_value_sanitizer' => $controllerValueSanitizer,
            'clear_when_controller_empty' => !empty($config['clear_when_controller_empty']),
        ];
    }

    public static function extractFieldGroupConfig(array $fieldGroupConfig): array
    {
        $extracted = self::extractConfigsFromFields((array) ($fieldGroupConfig['fields'] ?? []));
        $fieldGroupConfig['fields'] = $extracted['fields'];

        return [
            'field_group_config' => $fieldGroupConfig,
            'configs' => $extracted['configs'],
        ];
    }

    public static function resolveChoices(array $config, string $controllerValue): array
    {
        $controllerValue = self::sanitizeControllerValue($config, $controllerValue);
        if (self::shouldClearTargetForControllerValue($config, $controllerValue)) {
            return [];
        }

        $rawChoices = call_user_func($config['choices_resolver'], $controllerValue);

        if (!is_array($rawChoices)) {
            return [];
        }

        $choices = [];

        foreach ($rawChoices as $value => $label) {
            $normalizedValue = self::sanitizeValue($config, $value);

            if ($normalizedValue === '') {
                continue;
            }

            $choices[$normalizedValue] = RuntimeUtils::sanitizeText((string) $label);
        }

        return $choices;
    }

    public static function resolveSubmittedControllerValue(array $config): string
    {
        $submittedFields = $_POST['acf'] ?? null;

        if (is_array($submittedFields)) {
            foreach ($submittedFields as $fieldKey => $fieldValue) {
                if (!is_string($fieldKey) || $fieldKey === '' || !function_exists('acf_get_field')) {
                    continue;
                }

                $fieldDefinition = acf_get_field($fieldKey);

                if (!is_array($fieldDefinition) || RuntimeUtils::sanitizeKey((string) ($fieldDefinition['name'] ?? '')) !== $config['controller_field_name']) {
                    continue;
                }

                return self::sanitizeControllerValue($config, is_array($fieldValue) ? reset($fieldValue) : $fieldValue);
            }
        }

        return self::resolveCurrentControllerValue($config);
    }

    public static function normalizeSubmittedValues(mixed $value, array $config): array
    {
        if ($config['field_type'] === 'checkbox') {
            $values = is_array($value) ? $value : [$value];

            return array_values(array_filter(array_map(
                static fn (mixed $item): string => self::sanitizeValue($config, $item),
                $values
            )));
        }

        $normalizedValue = self::sanitizeValue($config, $value);

        return $normalizedValue === '' ? [] : [$normalizedValue];
    }

    public static function getAllowedChoiceValues(array $choices): array
    {
        return array_values(array_unique(array_keys($choices)));
    }

    public static function validateCheckboxValues(array $config, array $submittedValues, array $allowedValues): mixed
    {
        foreach ($submittedValues as $submittedValue) {
            if (!in_array($submittedValue, $allowedValues, true)) {
                return $config['invalid_value_message'];
            }
        }

        return true;
    }

    public static function validateScalarValue(array $config, string $submittedValue, array $allowedValues): mixed
    {
        if ($submittedValue === '') {
            return true;
        }

        return in_array($submittedValue, $allowedValues, true)
            ? true
            : $config['invalid_value_message'];
    }

    public static function shouldClearTargetForControllerValue(array $config, string $controllerValue): bool
    {
        return !empty($config['clear_when_controller_empty'])
            && self::sanitizeControllerValue($config, $controllerValue) === '';
    }

    public static function getClientConfigsForScreen(array $configs, string $hook, mixed $screen): array
    {
        $clientConfigs = [];

        foreach ($configs as $targetFieldName => $config) {
            if (!self::configMatchesScreen($config, $hook, $screen)) {
                continue;
            }

            $controllerValuesProvider = $config['controller_values_provider'];

            if (!is_callable($controllerValuesProvider)) {
                continue;
            }

            $choicesByControllerValue = [];

            foreach ((array) call_user_func($controllerValuesProvider) as $controllerValue) {
                $normalizedControllerValue = self::sanitizeControllerValue($config, $controllerValue);

                if ($normalizedControllerValue === '') {
                    continue;
                }

                $choicesByControllerValue[$normalizedControllerValue] = self::resolveChoices($config, $normalizedControllerValue);
            }

            $clientConfigs[$targetFieldName] = [
                'targetFieldName' => $targetFieldName,
                'controllerFieldName' => $config['controller_field_name'],
                'fieldType' => $config['field_type'],
                'choicesByControllerValue' => $choicesByControllerValue,
            ];
        }

        return $clientConfigs;
    }

    private static function extractConfigsFromFields(array $fields): array
    {
        $normalizedFields = [];
        $configs = [];

        foreach ($fields as $index => $field) {
            if (!is_array($field)) {
                $normalizedFields[$index] = $field;
                continue;
            }

            $normalizedField = $field;
            $dependentChoicesConfig = self::normalizeDependentChoicesConfig($normalizedField['dependent_choices'] ?? null);

            if ($dependentChoicesConfig !== null) {
                $targetFieldName = RuntimeUtils::sanitizeKey((string) ($normalizedField['name'] ?? ''));
                $fieldType = RuntimeUtils::sanitizeKey((string) ($normalizedField['type'] ?? ''));

                if ($targetFieldName !== '') {
                    $config = self::normalizeConfig(array_merge(
                        $dependentChoicesConfig,
                        [
                            'target_field_name' => $targetFieldName,
                            'field_type' => $dependentChoicesConfig['field_type'] ?? $fieldType,
                        ]
                    ));

                    if ($config !== null) {
                        $configs[$targetFieldName] = $config;
                    }
                }

                unset($normalizedField['dependent_choices']);
            }

            foreach (['fields', 'sub_fields'] as $nestedFieldKey) {
                if (!is_array($normalizedField[$nestedFieldKey] ?? null)) {
                    continue;
                }

                $nested = self::extractConfigsFromFields($normalizedField[$nestedFieldKey]);
                $normalizedField[$nestedFieldKey] = $nested['fields'];
                $configs = array_replace($configs, $nested['configs']);
            }

            if (is_array($normalizedField['layouts'] ?? null)) {
                $normalizedLayouts = [];

                foreach ($normalizedField['layouts'] as $layoutKey => $layout) {
                    if (!is_array($layout)) {
                        $normalizedLayouts[$layoutKey] = $layout;
                        continue;
                    }

                    if (is_array($layout['sub_fields'] ?? null)) {
                        $nested = self::extractConfigsFromFields($layout['sub_fields']);
                        $layout['sub_fields'] = $nested['fields'];
                        $configs = array_replace($configs, $nested['configs']);
                    }

                    $normalizedLayouts[$layoutKey] = $layout;
                }

                $normalizedField['layouts'] = $normalizedLayouts;
            }

            $normalizedFields[$index] = $normalizedField;
        }

        return [
            'fields' => $normalizedFields,
            'configs' => $configs,
        ];
    }

    private static function resolveCurrentControllerValue(array $config): string
    {
        $postId = RuntimeUtils::currentPostId();

        if ($postId <= 0 || !function_exists('get_field')) {
            return '';
        }

        return self::sanitizeControllerValue(
            $config,
            get_field($config['controller_field_name'], $postId)
        );
    }

    private static function sanitizeValue(array $config, mixed $value): string
    {
        return (string) call_user_func($config['value_sanitizer'], $value);
    }

    private static function sanitizeControllerValue(array $config, mixed $value): string
    {
        return (string) call_user_func($config['controller_value_sanitizer'], $value);
    }

    private static function normalizeDependentChoicesConfig(mixed $config): ?array
    {
        if (is_object($config) && method_exists($config, 'toArray')) {
            $config = $config->toArray();
        }

        return is_array($config) ? $config : null;
    }

    private static function configMatchesScreen(array $config, string $hook, mixed $screen): bool
    {
        if (is_callable($config['screen_matcher'])) {
            return (bool) call_user_func($config['screen_matcher'], $hook, $screen);
        }

        return in_array($hook, ['post.php', 'post-new.php'], true);
    }
}
