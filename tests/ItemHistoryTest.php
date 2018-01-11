<?php

namespace App\Tests;

use App\Item;
use App\User;

class ItemHistoryTest extends Base
{

    /** @var User */
    protected $testUser;

    public function setUp()
    {
        parent::setUp();
        $this->setUpDb();
        $this->testUser = new User($this->db);
        $this->testUser->register('Test User');
    }

    /**
     * @testdox An Item has an ID and title and has by default plain text contents.
     * @test
     */
    public function historyBasics()
    {
        $item = new Item(null, $this->testUser);
        $item->save(['title' => 'Test']);
        $this->assertCount(0, $item->getChangeSets());
    }
}
