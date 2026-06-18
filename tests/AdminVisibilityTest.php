<?php

namespace Tsp\AcfBuilder\Tests;

use Tsp\AcfBuilder\AdminVisibility;

class AdminVisibilityTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalizeSupportsSnakeAndCamelCaseKeys()
    {
        $this->assertSame([
            'fieldKey' => 'field_course_type',
            'operator' => '!=',
            'value' => 'unstructured',
        ], AdminVisibility::normalize([
            'field_key' => 'field_course_type',
            'operator' => '!=',
            'value' => 'unstructured',
        ]));

        $this->assertSame([
            'fieldName' => 'course_type',
            'operator' => '==',
            'value' => 'structured',
        ], AdminVisibility::normalize([
            'fieldName' => 'course_type',
            'operator' => 'invalid',
            'value' => 'structured',
        ]));
    }

    public function testApplyToWrapperAddsEncodedVisibilityRule()
    {
        $wrapper = AdminVisibility::applyToWrapper(['width' => '50'], [
            'field_key' => 'field_course_type',
            'operator' => '!=',
            'value' => 'unstructured',
        ]);

        $this->assertSame('50', $wrapper['width']);
        $this->assertSame(
            '{"fieldKey":"field_course_type","operator":"!=","value":"unstructured"}',
            $wrapper[AdminVisibility::WRAPPER_ATTRIBUTE]
        );
    }

    public function testNormalizeRejectsRulesWithoutAControllerField()
    {
        $this->assertNull(AdminVisibility::normalize([
            'operator' => '!=',
            'value' => 'unstructured',
        ]));
    }
}
