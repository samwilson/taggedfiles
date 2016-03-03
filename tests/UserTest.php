<?php

namespace App\Tests;

use App\User;

class UserTest extends Base
{

    public function testRegister()
    {
        // First.
        $user1 = new User($this->db);
        $user1->register('Name');
        $this->assertSame('Name', $user1->getName());
        // Second.
        $user2 = new User($this->db);
        $user2->register('Name');
        $this->assertSame('Name 2', $user2->getName());
        // Third.
        $user3 = new User($this->db);
        $user3->register('Name');
        $this->assertSame('Name 3', $user3->getName());
        // Make sure there were three users created.
        $this->assertSame('3', $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn());
    }

    public function testGroups()
    {
        $user1 = new User($this->db);
        $user1->register('Name');
        $this->assertSame('Name', $user1->getName());
        $this->assertSame(['Name'], $user1->getGroupNames());
    }
}
