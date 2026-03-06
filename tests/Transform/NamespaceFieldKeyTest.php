<?php

namespace Tsp\AcfBuilder\Tests\Transform;

use Tsp\AcfBuilder\FieldsBuilder;
use Tsp\AcfBuilder\Transform;

class NamespaceFieldKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testIsRecursive()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $transform = new Transform\NamespaceFieldKey($builder);

        $this->assertInstanceOf(Transform\RecursiveTransform::class, $transform);
    }

    public function testGetKeys()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $transform = new Transform\NamespaceFieldKey($builder);

        $this->assertSame(['key', 'field', 'collapsed'], $transform->getKeys());
    }

    public function testTransformValue()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $builder->method('getName')->willReturn('Fields Builder Name');

        $transform = new Transform\NamespaceFieldKey($builder);
        $this->assertSame('field_fields_builder_name_value', $transform->transformValue('field_value'));
        $this->assertSame('field_fields_builder_name_value', $transform->transformValue('group_value'));
    }



    public function testShouldTransformValue()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $builder->method('getName')->willReturn('Fields Builder Name');

        $transform = new Transform\NamespaceFieldKey($builder);

        $this->assertTrue($transform->shouldTransformValue('key', [
            'key' => 'field_name',
        ]));

        $this->assertFalse($transform->shouldTransformValue('key', [
            'key' => '1234859849584545',
            '_has_custom_key' => true,
        ]));

        $this->assertTrue($transform->shouldTransformValue('key', [
            'key' => 'field_name',
            '_has_custom_key' => false,
        ]));
    }
}
