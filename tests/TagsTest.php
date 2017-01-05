<?php

namespace App\Tests;

use App\TagsIdentifier;

class TagsTest extends Base
{
    public function testOperationsAndOrdering()
    {
        $tags = new TagsIdentifier();
        $this->assertEquals('', $tags->toString());
        $tags->add(3);
        $this->assertEquals('3', $tags->toString());
        $tags->add(2);
        $this->assertEquals('2,3', $tags->toString());
        $tags->remove(1);
        $this->assertEquals('2,3', $tags->toString());
        $tags->remove(3);
        $this->assertEquals('2', $tags->toString());
    }

    public function testCommaSeparatedStrings()
    {
        $tags = new TagsIdentifier();
        $tags->addFromString('2,3');
        $this->assertEquals('2,3', $tags->toString());
        $tags->addFromString('4,3');
        $this->assertEquals('2,3,4', $tags->toString());
        $this->assertEquals([2,3,4], $tags->toArray());
        $tags->removeFromString('3');
        $this->assertEquals('2,4', $tags->toString());
        $this->assertFalse($tags->isEmpty());
    }

    public function testNonIntegerIds()
    {
        $tags = new TagsIdentifier();
        $tags->add('test');
        $this->assertEquals('', $tags->toString());
        $this->assertTrue($tags->isEmpty());
    }
}
