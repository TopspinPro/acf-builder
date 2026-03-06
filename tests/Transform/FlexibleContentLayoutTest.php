<?php

namespace Tsp\AcfBuilder\Tests\Transform;

use Tsp\AcfBuilder\Builder;
use Tsp\AcfBuilder\Transform;

class FlexibleContentLayoutTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformValue()
    {
        $builder = $this->createMock(Builder::class);
        $transform = new Transform\FlexibleContentLayout($builder);

        $expected = [
            'sub_fields' => 'fields',
            'label' => 'title',
        ];

        $actual = $transform->transform([
            'fields' => 'fields',
            'title' => 'title',
        ]);

        $this->assertSame($expected, $actual);
    }
}
