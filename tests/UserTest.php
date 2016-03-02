<?php

namespace App\Tests;

use App\User;

class UserTest extends Base
{

    public function testName()
    {
        // First.
        $user1 = new User($this->db);
        $user1->save('Name');
        $this->assertSame('Name', $user1->getName());
        // Second.
        $user2 = new User($this->db);
        $user2->save('Name');
        $this->assertSame('Name (2)', $user2->getName());
        // Third.
        $user3 = new User($this->db);
        $user3->save('Name');
        $this->assertSame('Name (3)', $user3->getName());
        // Make sure there were three users created.
        $this->assertSame('3', $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn());
        // Change an existing user's name.
        $user2->save('User Two');
        $this->assertSame('User Two', $user2->getName());
        $this->assertSame('3', $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn());
    }
}
