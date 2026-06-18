<?php

namespace Tsp\AcfBuilder;

class DependentChoices implements Builder
{
    private array $config;

    private function __construct(string $fieldType)
    {
        $this->config = [
            'field_type' => self::sanitizeKey($fieldType),
        ];
    }

    public static function checkbox(): self
    {
        return new self('checkbox');
    }

    public static function select(): self
    {
        return new self('select');
    }

    public function controlledBy(string $fieldName): self
    {
        $this->config['controller_field_name'] = self::sanitizeKey($fieldName);

        return $this;
    }

    public function controllerValues(callable $provider): self
    {
        $this->config['controller_values_provider'] = $provider;

        return $this;
    }

    public function choices(callable $resolver): self
    {
        $this->config['choices_resolver'] = $resolver;

        return $this;
    }

    public function onScreen(callable $screenMatcher): self
    {
        $this->config['screen_matcher'] = $screenMatcher;

        return $this;
    }

    public function onPostType(string $postType): self
    {
        $postType = self::sanitizeKey($postType);

        return $this->onScreen(static function (string $hook, mixed $screen) use ($postType): bool {
            if ($postType === '' || !is_object($screen)) {
                return false;
            }

            return in_array($hook, ['post.php', 'post-new.php'], true)
                && self::sanitizeKey((string) ($screen->post_type ?? '')) === $postType;
        });
    }

    public function invalidMessage(string $message): self
    {
        $this->config['invalid_value_message'] = $message;

        return $this;
    }

    public function sanitizeController(callable $sanitizer): self
    {
        $this->config['controller_value_sanitizer'] = $sanitizer;

        return $this;
    }

    public function sanitizeControllerAsKey(): self
    {
        return $this->sanitizeController(static fn (mixed $rawValue): string => self::sanitizeKey((string) $rawValue));
    }

    public function sanitizeControllerAsUserId(): self
    {
        return $this->sanitizeController(static function (mixed $rawValue): string {
            if (!is_numeric($rawValue)) {
                return '';
            }

            $userId = abs((int) $rawValue);

            return $userId > 0 ? (string) $userId : '';
        });
    }

    public function sanitizeValue(callable $sanitizer): self
    {
        $this->config['value_sanitizer'] = $sanitizer;

        return $this;
    }

    public function sanitizeValueAsKey(): self
    {
        return $this->sanitizeValue(static fn (mixed $rawValue): string => self::sanitizeKey((string) $rawValue));
    }

    public function clearWhenControllerEmpty(bool $clear = true): self
    {
        $this->config['clear_when_controller_empty'] = $clear;

        return $this;
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function build()
    {
        return $this->toArray();
    }

    private static function sanitizeKey(string $value): string
    {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)) ?: '';
    }
}
