<?php

namespace Tsp\AcfBuilder\Tests\Runtime;

use Tsp\AcfBuilder\Runtime\DatePickerFields;

class DatePickerFieldsTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareFieldAddsDateMetadataToWrapper()
    {
        $field = DatePickerFields::_prepareField([
            'type' => 'date_picker',
            'min_date' => 'today',
            'max_date' => '20301231',
            'linked_date_field' => 'start_date',
        ]);

        $this->assertArraySubset([
            'wrapper' => [
                'data-min-date' => 'today',
                'data-max-date' => '20301231',
                'data-linked-field' => 'start_date',
            ],
        ], $field);
    }

    public function testPrepareFieldIgnoresUnconfiguredDatePickers()
    {
        $field = [
            'type' => 'date_picker',
        ];

        $this->assertSame($field, DatePickerFields::_prepareField($field));
    }
}
