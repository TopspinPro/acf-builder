<?php

namespace Tsp\AcfBuilder\Tests\Transform;

use Tsp\AcfBuilder\FieldBuilder;
use Tsp\AcfBuilder\FieldsBuilder;
use Tsp\AcfBuilder\Transform;

class ConditionalFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testIsRecursive()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $transform = new Transform\ConditionalField($builder);

        $this->assertInstanceOf(Transform\RecursiveTransform::class, $transform);
    }

    public function testGetKeys()
    {
        $builder = $this->createMock(FieldsBuilder::class);
        $transform = new Transform\ConditionalField($builder);

        $this->assertSame(['field'], $transform->getKeys());
    }

    public function testTransformValue()
    {
        $field = $this->createMock(FieldBuilder::class);
        $field->method('getKey')->willReturn('field_key');

        $builder = $this->createMock(FieldsBuilder::class);
        $builder->method('getField')->with('value')->willReturn($field);
        $builder->method('fieldExists')->with('value')->willReturn(true);

        $transform = new Transform\ConditionalField($builder);
        $this->assertSame('field_key', $transform->transformValue('value'));
    }
}
