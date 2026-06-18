<?php

namespace Tsp\AcfBuilder;

class AdminVisibility
{
    public const WRAPPER_ATTRIBUTE = 'data-tsp-acf-visible-if';

    public static function normalize(mixed $rule): ?array
    {
        if (!is_array($rule)) {
            return null;
        }

        if (isset($rule['visible_if']) && is_array($rule['visible_if'])) {
            $rule = $rule['visible_if'];
        }

        $fieldKey = self::sanitizeKey($rule['field_key'] ?? $rule['fieldKey'] ?? '');
        $fieldName = self::sanitizeKey($rule['field_name'] ?? $rule['fieldName'] ?? '');

        if ($fieldKey === '' && $fieldName === '') {
            return null;
        }

        $operator = (string) ($rule['operator'] ?? '==');

        if (!in_array($operator, ['==', '!='], true)) {
            $operator = '==';
        }

        return array_filter([
            'fieldKey' => $fieldKey,
            'fieldName' => $fieldName,
            'operator' => $operator,
            'value' => self::sanitizeText($rule['value'] ?? ''),
        ], static fn (mixed $value): bool => $value !== '');
    }

    public static function encode(mixed $rule): ?string
    {
        $normalizedRule = self::normalize($rule);

        if ($normalizedRule === null) {
            return null;
        }

        $encodedRule = json_encode($normalizedRule);

        return is_string($encodedRule) ? $encodedRule : null;
    }

    public static function applyToWrapper(array $wrapper, mixed $rule): array
    {
        $encodedRule = self::encode($rule);

        if ($encodedRule !== null) {
            $wrapper[self::WRAPPER_ATTRIBUTE] = $encodedRule;
        }

        return $wrapper;
    }

    public static function sanitizeKey(mixed $value): string
    {
        if (function_exists('sanitize_key')) {
            return sanitize_key((string) $value);
        }

        return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)) ?: '';
    }

    public static function sanitizeText(mixed $value): string
    {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field((string) $value);
        }

        return trim(strip_tags((string) $value));
    }
}
