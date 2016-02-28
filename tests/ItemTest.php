<?php

use App\Item;
use App\App;
use App\Db;

class ItemTest extends \PHPUnit_Framework_TestCase {

    /** @var \App\Db */
    protected $db;

    public function setUp() {
        $this->db = new Db();
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS `keywords`");
        $this->db->query("DROP TABLE IF EXISTS `items`");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        $this->db->install();
        App::deleteDir(__DIR__.'/tests');
    }

    /**
     * @testdox An Item has an ID and title.
     * @test
     */
    public function basics() {
        $item = new Item();
        $item->save(['title' => 'Test']);
        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Test', $item->getTitle());
        $this->assertEquals(1, $this->db->query("SELECT COUNT(*) FROM `items`")->fetchColumn());
    }

    /**
     * @testdox An item can be created and modified.
     */
    public function modification() {
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
    public function keywords() {
        $item = new Item();
        $item->save([], 'one,two');
        $this->assertCount(2, $item->getKeywords());
    }

    /**
     * @testdox An item can have a single file attached.
     * @test
     */
    public function files() {
        $item = new Item();
        // First version.
        $item->save([], null, null, 'Test file contents.');
        $this->assertSame(1, $item->getId());
        $this->assertSame('c4/ca/v1', $item->getFilePath());
        $this->assertFileExists(__DIR__.'/data/storage/c4/ca/v1');
        $this->assertSame('Test file contents.', $item->getFileContents());
        $this->assertSame(1, $item->getVersionCount());
        // Second version.
        $item->save(['id'=>1], null, null, 'New file contents.');
        $this->assertSame(1, $item->getId());
        $this->assertSame('c4/ca/v2', $item->getFilePath());
        $this->assertFileExists(__DIR__.'/data/storage/c4/ca/v1');
        $this->assertFileExists(__DIR__.'/data/storage/c4/ca/v2');
        $this->assertSame('New file contents.', $item->getFileContents());
        $this->assertSame('Test file contents.', $item->getFileContents(1));
    }

}
