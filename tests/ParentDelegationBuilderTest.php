<?php

namespace Tsp\AcfBuilder\Tests;

use Tsp\AcfBuilder\Builder;

class ParentDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testReturningParent()
    {
        $parent = $this
            ->getMockBuilder('Tsp\AcfBuilder\ParentDelegationBuilder')
            ->setMethods(['parentMethod', 'build'])
            ->getMockForAbstractClass();
        $child = $this->getMockForAbstractClass('Tsp\AcfBuilder\ParentDelegationBuilder');
        $child->setParentContext($parent);

        $parent->expects($this->once())->method('parentMethod');
        $child->parentMethod();
    }

    public function testThrowingException()
    {
        $parent = $this->getMockForAbstractClass('Tsp\AcfBuilder\ParentDelegationBuilder');
        $child = $this->getMockForAbstractClass('Tsp\AcfBuilder\ParentDelegationBuilder');
        $child->setParentContext($parent);

        $this->setExpectedException('\Exception');
        $child->nonExistantParentMethod();
    }
}
