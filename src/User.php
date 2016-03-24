<?php

namespace App;

class User
{

    /** @var integer The ID of the public group. */
    const GROUP_PUBLIC = 1;

    /** @var integer The ID of the admin group. */
    const GROUP_ADMIN = 2;

    /** @var \App\Db */
    protected $db;

    /** @var stdClass */
    protected $data;

    public function __construct($database = null)
    {
        $this->db = ($database) ? $database : new Db();
    }

    public function register($name, $email = null, $password = null)
    {
        $params = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        // Add the user.
        $userCount = 'SELECT COUNT(*) FROM `users` WHERE `name` LIKE :name';
        $userNum = $this->db->query($userCount, ['name' => "$name%"])->fetchColumn();
        $userName = ($userNum > 0) ? $name . ' ' . ($userNum + 1) : $name;
        $params['name'] = $userName;
        $this->db->query("INSERT INTO users SET name=:name, email=:email, password=:password", $params);
        $userId = $this->db->lastInsertId();

        // Add the new user to a group of their own.
        $groupCountSql = 'SELECT COUNT(*) FROM `groups` WHERE `name` LIKE :name';
        $groupNum = $this->db->query($groupCountSql, ['name' => "$name%"])->fetchColumn();
        $groupName = ($groupNum > 0) ? $name . ' ' . ($groupNum + 1) : $name;
        $this->db->query('INSERT INTO `groups` SET `name`=:name', ['name' => $groupName]);
        $personalGroupId = $this->db->lastInsertId();
        $groupMemberSql = 'INSERT INTO `user_groups` SET `user`=:u, `group`=:g';
        $this->db->query($groupMemberSql, ['u' => $userId, 'g' => $personalGroupId]);
        // Make it their default group.
        $this->db->query("UPDATE `users` SET `default_group` = :g WHERE `id`=:u", ['g'=>$personalGroupId,'u'=>$userId]);

        // Also add them to the public group.
        $groupMemberSql = 'INSERT INTO `user_groups` SET `user`=:u, `group`=:g';
        $this->db->query($groupMemberSql, ['u' => $userId, 'g' => self::GROUP_PUBLIC]);

        // Reload the user's data.
        $this->load($userId);
    }

    /**
     * Get a list of all groups that this user belongs to.
     * @return string[] Each with 'id' and 'name' properties.
     */
    public function getGroups()
    {
        if (!$this->getId()) {
            $sql = "SELECT `id`, `name` FROM `groups` WHERE `id` = " . self::GROUP_PUBLIC;
        } else {
            $sql = "SELECT g.id, g.name FROM users u "
                . " JOIN user_groups ug ON ug.user = u.id "
                . " JOIN `groups` g ON ug.group = g.id"
                . " WHERE u.id = :id";
        }
        $groups = [];
        $results = $this->db->query($sql, ['id' => $this->getId()])->fetchAll();
        foreach ($results as $res) {
            $groups[] = ['id' => (int)$res->id, 'name' => $res->name];
        }
        return $groups;
    }

    public function getReminder()
    {
        if (!$this->getId()) {
            return false;
        }
        $sql = "UPDATE users SET reminder_token=:t, reminder_time=NOW() WHERE id=:id";
        $unhashedToken = md5(time());
        $params = [
            't' => password_hash($unhashedToken, PASSWORD_DEFAULT),
            'id' => $this->getId(),
        ];
        $this->db->query($sql, $params);
        return $unhashedToken;
    }

    public function checkReminderToken($token)
    {
        return password_verify($token, $this->data->reminder_token);
    }

    public function changePassword($password)
    {
        if (!$this->getId()) {
            return false;
        }
        $sql = "UPDATE users SET password=:pwd, reminder_token=NULL, reminder_time=NULL WHERE id=:id";
        $params = [
            'pwd' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $this->getId(),
        ];
        $this->db->query($sql, $params);
    }

    public function load($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $this->data = $this->db->query($sql, ['id' => $id])->fetch();
    }

    public function loadByName($name)
    {
        $sql = "SELECT * FROM users WHERE name = :name";
        $this->data = $this->db->query($sql, ['name' => $name])->fetch();
    }

    public function getId()
    {
        return (isset($this->data->id)) ? (int) $this->data->id : false;
    }

    public function getName()
    {
        return isset($this->data->name) ? $this->data->name : false;
    }

    public function getEmail()
    {
        return isset($this->data->email) ? $this->data->email : false;
    }

    public function getDefaultGroup()
    {
        if (isset($this->data->default_group)) {
            $sql = "SELECT * FROM groups WHERE id = :id";
            $group = $this->db->query($sql, ['id'=>$this->data->default_group])->fetch();
            return $group;
        }
        return self::GROUP_PUBLIC;
    }
}
