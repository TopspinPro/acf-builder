<?php

namespace Tsp\AcfBuilder\Tests;

use Tsp\AcfBuilder\FieldBuilder;
use Tsp\AcfBuilder\DependentChoices;

class FieldBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Tsp\AcfBuilder\FieldBuilder'));
    }

    public function testGetName()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $this->assertSame('my_field', $subject->getName());
    }

    public function testBuild()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
        ], $subject->build());
    }

    public function testSetKey()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setKey('field_new_key'));
        $this->assertArraySubset([
            'key' => 'field_new_key',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
        ], $subject->build());
    }

    public function testSetKeyWithOutFieldPrepended()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setKey('new_key'));
        $this->assertArraySubset([
            'key' => 'field_new_key',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
        ], $subject->build());
    }

    public function testSetConfig()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setConfig('prepend', '@'));
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '@',
        ], $subject->build());
    }

    public function testUpdateConfig()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->updateConfig([
            'prepend' => '@',
            'label' => 'My New Label',
        ]));
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My New Label',
            'type' => 'text',
            'prepend' => '@',
        ], $subject->build());
    }

    public function testSetRequired()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setRequired());
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
            'required' => 1,
        ], $subject->build());

        $this->assertSame($subject, $subject->setUnrequired());
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
            'required' => 0,
        ], $subject->build());

        $this->assertSame($subject, $subject->setRequired());
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
            'required' => 1,
        ], $subject->build());
    }

    public function testSetLabel()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setLabel('My Label'));
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Label',
            'type' => 'text',
            'prepend' => '$',
        ], $subject->build());
    }

    public function testSetInstructions()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setInstructions('My Instructions'));
        $this->assertArraySubset([
            'key' => 'field_my_field',
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
            'prepend' => '$',
            'instructions' => 'My Instructions',
        ], $subject->build());
    }

    public function testSetDefaultValue()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertSame($subject, $subject->setDefaultValue('My Default'));
        $this->assertArraySubset([
                'key' => 'field_my_field',
                'name' => 'my_field',
                'label' => 'My Field',
                'type' => 'text',
                'prepend' => '$',
                'default_value' => 'My Default',
            ], $subject->build());
    }

    public function testDependentChoices()
    {
        $subject = new FieldBuilder('profile', 'select');
        $subject->dependentChoices(
            DependentChoices::select()
                ->controlledBy('author')
                ->choices([self::class, 'resolveChoices'])
        );

        $this->assertArraySubset([
            'dependent_choices' => [
                'field_type' => 'select',
                'controller_field_name' => 'author',
                'choices_resolver' => [self::class, 'resolveChoices'],
            ],
        ], $subject->build());
    }

    public function testDatePickerRuntimeHelpers()
    {
        $subject = new FieldBuilder('starts_at', 'date_picker');
        $subject
            ->minDate('today')
            ->maxDate('20301231')
            ->linkedDateField('campaign_start');

        $this->assertArraySubset([
            'min_date' => 'today',
            'max_date' => '20301231',
            'linked_date_field' => 'campaign_start',
        ], $subject->build());
    }

    public function testAdminVisibleIf()
    {
        $subject = new FieldBuilder('profile', 'select');
        $subject->adminVisibleIf([
            'field_key' => 'field_author',
            'operator' => '!=',
            'value' => '',
        ]);

        $this->assertSame(
            '{"fieldKey":"field_author","operator":"!="}',
            $subject->getWrapper()['data-tsp-acf-visible-if']
        );
    }

    public function testGetWrapper()
    {
        $wrapper = [
            'class' => 'foo',
            'id'    => 'bar',
        ];
        $subject = new FieldBuilder('my_field', 'text', ['wrapper' => $wrapper]);
        $this->assertSame($subject, $subject->setConfig('prepend', '@'));
        $this->assertArraySubset($wrapper, $subject->getWrapper());
    }

    public function testSetWrapper()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $this->assertSame($subject, $subject->setWrapper(['class' => 'foo', 'id' => 'bar']));
        $this->assertArraySubset([
            'key'     => 'field_my_field',
            'name'    => 'my_field',
            'label'   => 'My Field',
            'type'    => 'text',
            'wrapper' => [
                'class' => 'foo',
                'id'    => 'bar',
            ],
        ], $subject->build());
    }

    public function testSetWidth()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $this->assertSame($subject, $subject->setWidth('50%'));
        $this->assertArraySubset([
            'key'     => 'field_my_field',
            'name'    => 'my_field',
            'label'   => 'My Field',
            'type'    => 'text',
            'wrapper' => [
                'width' => '50%',
            ],
        ], $subject->build());
    }

    public function testSetAttr()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $this->assertSame($subject, $subject->setAttr('data-my_attr', 'My Attr'));
        $this->assertArraySubset([
            'key'     => 'field_my_field',
            'name'    => 'my_field',
            'label'   => 'My Field',
            'type'    => 'text',
            'wrapper' => [
                'data-my_attr' => 'My Attr',
            ],
        ], $subject->build());
    }

    public function testSetSelector()
    {
        $subject = new FieldBuilder('my_field', 'text');
        // returns FieldBuilder.
        $this->assertSame($subject, $subject->setSelector('.my-class'));

        // only id.
        $subject->setSelector('#my-id');
        $this->assertArraySubset([
            'id' => 'my-id',
        ], $subject->getWrapper());

        // only class.
        $subject->setSelector('.my-class');
        $this->assertArraySubset([
            'class' => 'my-class',
        ], $subject->getWrapper());

        // only class multiple.
        $subject->setSelector('.class1.class2');
        $this->assertArraySubset([
            'class' => 'class1 class2',
        ], $subject->getWrapper());

        // id / class.
        $subject->setSelector('#my-id.my-class');
        $this->assertArraySubset([
            'id'    => 'my-id',
            'class' => 'my-class',
        ], $subject->getWrapper());

        // id / class multiple.
        $subject->setSelector('#my-id.my-class.more-class');
        $this->assertArraySubset([
            'id'    => 'my-id',
            'class' => 'my-class more-class',
        ], $subject->getWrapper());

        // class /id.
        $subject->setSelector('.my-class#my-id');
        $this->assertArraySubset([
            'id'    => 'my-id',
            'class' => 'my-class',
        ], $subject->getWrapper());

        // class multiple /id.
        $subject->setSelector('.my-class.more-class#my-id');
        $this->assertArraySubset([
            'id'    => 'my-id',
            'class' => 'my-class more-class',
        ], $subject->getWrapper());
    }

    public function testConditional()
    {
        $subject = new FieldBuilder('my_field', 'text', ['prepend' => '$']);
        $this->assertNotSame($subject, $subject->conditional('other_field', '==', '1'));

        $this->assertArraySubset([
                'key' => 'field_my_field',
                'name' => 'my_field',
                'label' => 'My Field',
                'type' => 'text',
                'prepend' => '$',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'other_field',
                            'operator'  =>  '==',
                            'value' => '1',
                        ],
                    ]
                ],
            ], $subject->build());
    }

    public function testSetCustomKey()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $subject->setCustomKey('129384192384912384');

        $this->assertArraySubset([
            'key' => '129384192384912384',
            '_has_custom_key' => true,
            'name' => 'my_field',
            'label' => 'My Field',
            'type' => 'text',
        ], $subject->build());

        $this->assertArrayHasKey('_has_custom_key', $subject->build());
        $this->assertTrue($subject->hasCustomKey());
    }


    public function testNotCustomKey()
    {
        $subject = new FieldBuilder('my_field', 'text');
        $this->assertArrayNotHasKey('_has_custom_key', $subject->build());
        $this->assertFalse($subject->hasCustomKey());
    }

    public function testEnableBidirectionalWithSingleTarget()
    {
        $subject = new FieldBuilder('related_posts', 'relationship');
        $subject->enableBidirectional('field_related_content');

        $this->assertArraySubset([
            'name' => 'related_posts',
            'type' => 'relationship',
            'bidirectional' => 1,
            'bidirectional_target' => ['field_related_content'],
        ], $subject->build());
    }

    public function testEnableBidirectionalNormalizesTargets()
    {
        $subject = new FieldBuilder('authors', 'post_object');
        $subject->enableBidirectional([' field_author_profile ', '', null, 'field_featured_author']);

        $this->assertSame([
            'field_author_profile',
            'field_featured_author',
        ], $subject->build()['bidirectional_target']);
    }

    public static function resolveChoices()
    {
        return ['default' => 'Default'];
    }
}
