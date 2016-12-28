<?php

namespace App\Tests;

use App\Item;
use App\Config;
use Intervention\Image\ImageManagerStatic as Image;

class ItemTest extends Base
{

    /** @var \App\User */
    protected $testUser;

    public function setUp()
    {
        parent::setUp();
        $this->testUser = new \App\User($this->db);
        $this->testUser->register('Test User');
    }

    /**
     * @testdox An Item has an ID and title.
     * @test
     */
    public function basics()
    {
        $item = new Item(null, $this->testUser);
        $item->save(['title' => 'Test']);
        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Test', $item->getTitle());
        $this->assertEquals(1, $this->db->query("SELECT COUNT(*) FROM `items`")->fetchColumn());
    }

    public function testDates()
    {
        $item = new Item(null, $this->testUser);
        $item->save(['title' => 'Test', 'date' => '2013-01-12 13:45']);
        $this->assertEquals('2013-01-12 13:45:00', $item->getDate());
        $this->assertEquals(Item::DATE_GRANULARITY_DEFAULT, $item->getDateGranularity());

        $item->save(['title' => 'Test', 'date' => '2013-01-12 13:45', 'date_granularity' => 3]);
        $this->assertEquals('2013-01-12 13:45:00', $item->getDate());
        $this->assertEquals(3, $item->getDateGranularity());
        $this->assertEquals('January 2013', $item->getDateFormatted());

        $item->save(['title' => 'Test', 'date' => '2013-01-12 13:45', 'date_granularity' => 5]);
        $this->assertEquals('c. 2013', $item->getDateFormatted());
    }

    /**
     * @testdox An item can be created and modified.
     */
    public function modification()
    {
        $item = new Item(null, $this->testUser);
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
    public function tags()
    {
        $item = new Item(null, $this->testUser);
        $item->save([], 'one,two');
        $this->assertCount(2, $item->getTags());
    }

    /**
     * @testdox An item can have a single file attached.
     * @test
     */
    public function files()
    {
        $item = new Item(null, $this->testUser);
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
        $item2 = new Item(null, $this->testUser);
        $item2->save(['title' => 'Second test'], null, null, 'Second test.');
        $this->assertSame(2, $item2->getId());
        $this->assertSame('c8/1e/2/v1', $item2->getFilePath());
        $this->assertTrue($item2->isText());
    }

    public function testLocalCacheFile()
    {
        $config = new Config();
        $this->assertArrayHasKey('cache', $config->filesystems());
        $item = new Item(null, $this->testUser);
        $item->save([], null, null, 'Test file contents.');
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR
            . 'cache' . DIRECTORY_SEPARATOR . '1_v1_o', $item->getCachePath());
        $this->assertFileExists(__DIR__ . '/data/cache/1_v1_o');
    }

    /**
     * @testdox A text file can be updated to be a text file.
     * @test
     */
    public function fileModifyTextToText()
    {
        // Create an item, and save it twice with different text content.
        $item = new Item(null, $this->testUser);
        $item->save([], null, null, 'First contents');
        $this->assertEquals('First contents', $item->getFileContents());
        $item->save(null, null, null, 'Second contents');
        $this->assertEquals('Second contents', $item->getFileContents());

        // Check that the version numbers and mime types are what we'd expect.
        $this->assertEquals('First contents', $item->getFileContents(1));
        $this->assertEquals('text/plain', $item->getMimeType(1));
        $this->assertEquals('Second contents', $item->getFileContents(2));
        $this->assertEquals('text/plain', $item->getMimeType(2));
    }

    /**
     * @testdox A text file can be updated to be an image file.
     * @test
     */
    public function fileModifyTextToImage()
    {
        $img = Image::canvas(200, 100, '#ccc');
        $tmpFilename = $this->dataDir() . '/test-image.jpg';
        $img->save($tmpFilename);

        // Create an item, and save it twice with different text content.
        $item = new Item(null, $this->testUser);
        $item->save([], null, null, 'First contents');
        $this->assertEquals('First contents', $item->getFileContents());
        $item->save(null, null, $tmpFilename);
        $this->assertFileEquals($tmpFilename, $item->getCachePath());

        // Check that the version numbers and mime types are what we'd expect.
        $this->assertEquals('First contents', $item->getFileContents(1));
        $this->assertEquals('text/plain', $item->getMimeType(1));
        $this->assertFileEquals($tmpFilename, $item->getCachePath('o', 2));
        $this->assertEquals('image/jpeg', $item->getMimeType(2));
    }

    /**
     * @testdox Image thumbnails are correctly resized.
     * @test
     */
    public function correctSizedThumbnails()
    {
        // Create a large image.
        $img = Image::canvas(1000, 400, '#ccc');
        $tmpFilename = $this->dataDir() . '/test-image.jpg';
        $img->save($tmpFilename);
        // Add it to an Item.
        $item = new Item(null, $this->testUser);
        $item->save(null, null, $tmpFilename);
        // Check that the various sizes returned are correct.
        $this->assertEquals('image/jpeg', $item->getMimeType());
        $this->assertFileEquals($tmpFilename, $item->getCachePath('o'));
        // Load the 'display' size.
        $display = Image::make($item->getCachePath('d'));
        $this->assertEquals(700, $display->getWidth());
        $this->assertEquals(280, $display->getHeight());
        // The thumbnail is always 200 x 200.
        $thumb = Image::make($item->getCachePath('t'));
        $this->assertEquals(200, $thumb->getWidth());
        $this->assertEquals(200, $thumb->getHeight());
    }
}
