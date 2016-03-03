<?php

namespace App\Tests;

use App\Item;
use App\Config;

class ItemTest extends Base
{

    /**
     * @testdox An Item has an ID and title.
     * @test
     */
    public function basics()
    {
        $item = new Item();
        $item->save(['title' => 'Test']);
        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Test', $item->getTitle());
        $this->assertEquals(1, $this->db->query("SELECT COUNT(*) FROM `items`")->fetchColumn());
    }

    /**
     * @testdox An item can be created and modified.
     */
    public function modification()
    {
        $item = new Item();
        $item->save(['title' => 'Test']);
        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Test', $item->getTitle());
        $this->assertEquals(1, $this->db->query("SELECT COUNT(*) FROM `items`")->fetchColumn());
        $item->save(['id' => 1, 'title' => 'Testing Title']);
        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Testing Title', $item->getTitle());
        $this->assertEquals(1, $this->db->query("SELECT COUNT(*) FROM `items`")->fetchColumn());
    }

    /**
     * @testdox
     * @test
     */
    public function keywords()
    {
        $item = new Item();
        $item->save([], 'one,two');
        $this->assertCount(2, $item->getTags());
    }

    /**
     * @testdox An item can have a single file attached.
     * @test
     */
    public function files()
    {
        $item = new Item();
        // First version.
        $item->save([], null, null, 'Test file contents.');
        $this->assertSame(1, $item->getId());
        $this->assertSame('c4/ca/1/v1', $item->getFilePath());
        $this->assertFileExists(__DIR__ . '/data/storage/c4/ca/1/v1');
        $this->assertSame('Test file contents.', $item->getFileContents());
        $this->assertSame(1, $item->getVersionCount());
        // Second version.
        $item->save(['id' => 1], null, null, 'New file contents.');
        $this->assertSame(1, $item->getId());
        $this->assertSame(2, $item->getVersionCount());
        $this->assertSame('c4/ca/1/v2', $item->getFilePath());
        $this->assertFileExists(__DIR__ . '/data/storage/c4/ca/1/v1');
        $this->assertFileExists(__DIR__ . '/data/storage/c4/ca/1/v2');
        $this->assertSame('New file contents.', $item->getFileContents());
        $this->assertSame('Test file contents.', $item->getFileContents(1));
        // Upload a different file.
        $item2 = new Item();
        $item2->save(['title' => 'Second test'], null, null, 'Second test.');
        $this->assertSame(2, $item2->getId());
        $this->assertSame('c8/1e/2/v1', $item2->getFilePath());
        $this->assertTrue($item2->isText());
    }

    public function testLocalCacheFile()
    {
        $config = new Config();
        $this->assertArrayHasKey('cache', $config->filesystems());
        $item = new Item();
        $item->save([], null, null, 'Test file contents.');
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR
            . 'cache' . DIRECTORY_SEPARATOR . '1_v1_o', $item->getCachePath());
        $this->assertFileExists(__DIR__ . '/data/cache/1_v1_o');
    }
}
