<?php

namespace App\Tests;

use App\User;

class UserTest extends Base
{

    /**
     * A new User can be registered. If they supply a name that already exists, a number will be appended.
     * @test
     */
    public function register()
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

    /**
     * The default non-registered, non-authenticated User has no ID and is a member of the public group (which is also their default group).
     * @test
     */
    public function publicGroup()
    {
        $user = new User($this->db);
        $this->assertEquals(User::GROUP_PUBLIC, $user->getDefaultGroup());
        $groups = $user->getGroups();
        $this->assertCount(1, $groups);
        $group = $groups[0];
        $this->assertEquals(User::GROUP_PUBLIC, $group['id']);
    }

    /**
     * When a User registers, a new Group is created of which they are the only member.
     * They are also a member of the Public group.
     * @test
     */
    public function ownGroup()
    {
        $user1 = new User($this->db);
        $user1->register('Name');
        $this->assertSame('Name', $user1->getName());
        $groups = $user1->getGroups();
        $this->assertCount(2, $groups);
    }

    /**
     * A user has a 'default group', which initially is the same as the group that is created for them when
     * they register.
     * @test
     */
    public function testGroupMembership()
    {
        $user1 = new User($this->db);
        $user1->register('Name');
        $defaultGroup = $user1->getDefaultGroup();
        $this->assertSame('Name', $defaultGroup->name);
    }
}
