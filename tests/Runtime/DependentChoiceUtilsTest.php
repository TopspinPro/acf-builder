<?php

namespace Tsp\AcfBuilder\Tests\Runtime;

use Tsp\AcfBuilder\DependentChoices;
use Tsp\AcfBuilder\Runtime\Support\DependentChoiceUtils;

class DependentChoiceUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractFieldGroupConfigRemovesFieldMetadataAndRegistersConfig()
    {
        $extracted = DependentChoiceUtils::extractFieldGroupConfig([
            'fields' => [
                [
                    'name' => 'profile',
                    'type' => 'select',
                    'dependent_choices' => DependentChoices::select()
                        ->controlledBy('author')
                        ->controllerValues([self::class, 'controllerValues'])
                        ->choices([self::class, 'choices'])
                        ->sanitizeControllerAsUserId()
                        ->sanitizeValueAsKey()
                        ->clearWhenControllerEmpty(),
                ],
            ],
        ]);

        $this->assertArrayNotHasKey('dependent_choices', $extracted['field_group_config']['fields'][0]);
        $this->assertArraySubset([
            'profile' => [
                'target_field_name' => 'profile',
                'controller_field_name' => 'author',
                'field_type' => 'select',
                'controller_values_provider' => [self::class, 'controllerValues'],
                'choices_resolver' => [self::class, 'choices'],
                'clear_when_controller_empty' => true,
            ],
        ], $extracted['configs']);
    }

    public function testResolveChoicesUsesSeparateControllerAndValueSanitizers()
    {
        $config = DependentChoiceUtils::normalizeConfig([
            'target_field_name' => 'profile',
            'controller_field_name' => 'author',
            'field_type' => 'select',
            'choices_resolver' => [self::class, 'choices'],
            'controller_value_sanitizer' => static fn (mixed $value): string => is_numeric($value) ? (string) abs((int) $value) : '',
            'value_sanitizer' => static fn (mixed $value): string => strtolower((string) $value),
        ]);

        $this->assertSame([
            'default' => 'Default',
            'tennis' => 'Tennis',
        ], DependentChoiceUtils::resolveChoices($config, '2066'));
    }

    public function testClearWhenControllerEmptyReturnsNoChoices()
    {
        $config = DependentChoiceUtils::normalizeConfig([
            'target_field_name' => 'profile',
            'controller_field_name' => 'author',
            'field_type' => 'select',
            'choices_resolver' => [self::class, 'choices'],
            'controller_value_sanitizer' => static fn (mixed $value): string => is_numeric($value) ? (string) abs((int) $value) : '',
            'clear_when_controller_empty' => true,
        ]);

        $this->assertSame([], DependentChoiceUtils::resolveChoices($config, ''));
    }

    public static function controllerValues()
    {
        return ['2066'];
    }

    public static function choices(mixed $controllerValue = null)
    {
        unset($controllerValue);

        return [
            'Default' => 'Default',
            'Tennis' => 'Tennis',
        ];
    }
}
