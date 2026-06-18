<?php

namespace Tsp\AcfBuilder\Tests;

use Tsp\AcfBuilder\DependentChoices;

class DependentChoicesTest extends \PHPUnit_Framework_TestCase
{
    public function testSelectBuildsConfig()
    {
        $choices = DependentChoices::select()
            ->controlledBy('course_author')
            ->controllerValues([self::class, 'provideControllerValues'])
            ->choices([self::class, 'resolveChoices'])
            ->invalidMessage('Choose a valid profile.')
            ->sanitizeControllerAsUserId()
            ->sanitizeValueAsKey()
            ->clearWhenControllerEmpty();

        $config = $choices->build();

        $this->assertArraySubset([
            'field_type' => 'select',
            'controller_field_name' => 'course_author',
            'controller_values_provider' => [self::class, 'provideControllerValues'],
            'choices_resolver' => [self::class, 'resolveChoices'],
            'invalid_value_message' => 'Choose a valid profile.',
            'clear_when_controller_empty' => true,
        ], $config);
        $this->assertTrue(is_callable($config['controller_value_sanitizer']));
        $this->assertTrue(is_callable($config['value_sanitizer']));
        $this->assertSame('2066', $config['controller_value_sanitizer']('2066'));
        $this->assertSame('', $config['controller_value_sanitizer'](''));
        $this->assertSame('tenniscoach', $config['value_sanitizer']('Tennis Coach!'));
    }

    public function testOnPostTypeBuildsCallableScreenMatcher()
    {
        $choices = DependentChoices::checkbox()
            ->controlledBy('audience')
            ->choices([self::class, 'resolveChoices'])
            ->onPostType('tsp_announcement')
            ->build();

        $this->assertTrue(is_callable($choices['screen_matcher']));
        $this->assertTrue($choices['screen_matcher']('post.php', (object) [
            'post_type' => 'tsp_announcement',
        ]));
        $this->assertFalse($choices['screen_matcher']('post.php', (object) [
            'post_type' => 'page',
        ]));
    }

    public static function provideControllerValues()
    {
        return ['one'];
    }

    public static function resolveChoices()
    {
        return ['one' => 'One'];
    }
}
