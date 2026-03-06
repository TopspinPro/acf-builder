<?php

namespace Tsp\AcfBuilder\Tests;

use Tsp\AcfBuilder\FieldsBuilder;
use Tsp\AcfBuilder\FieldNameCollisionException;

class FieldNameCollisionExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Tsp\AcfBuilder\FieldNameCollisionException'));
    }

    public function testExceptionThrownDuringFieldNameCollision()
    {
        $this->expectException(\Tsp\AcfBuilder\FieldNameCollisionException::class);

        $builder = new FieldsBuilder('Banner');
        $builder
            ->addText('title')
            ->addWysiwyg('content')
            ->addTextarea('content');
    }

    public function testExceptionThrownDuringFieldNameCollisionUsingRepeaters()
    {
        $this->expectException(\Tsp\AcfBuilder\FieldNameCollisionException::class);

        $builder = new FieldsBuilder('Banner');
        $builder
            ->addText('title')
            ->addRepeater('slides')
                ->addRadio('slides')
                    ->addChoices(1, 2, 3, 4)
                ->endRepeater()
            ->addRadio('slides')
                ->addChoices(1, 2, 3, 4);
    }

    public function testExceptionThrownDuringFieldNameCollisionUsingFlexibleContent()
    {
        $this->expectException(\Tsp\AcfBuilder\FieldNameCollisionException::class);

        $builder = new FieldsBuilder('Banner');
        $builder
            ->addText('title')
            ->addFlexibleContent('content')
                ->addLayout('copy')
                    ->addWysiwyg('content')
                ->addLayout('gallery')
                    ->addRepeater('images')
                        ->addImage('image')
                ->endFlexibleContent()
            ->addWysiwyg('content');
    }

    public function testExceptionThrownDuringFieldNameCollisionUsingAddFields()
    {
        $this->expectException(\Tsp\AcfBuilder\FieldNameCollisionException::class);

        $builder = new FieldsBuilder('Banner');
        $builder
            ->addText('title')
            ->addWysiwyg('content');

        $builder2 = new FieldsBuilder('Section');
        $builder2
            ->addText('headline')
            ->addWysiwyg('content');

        $builder->addFields($builder2);
    }
}
