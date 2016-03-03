<?php

namespace App;

class User {

    /** @var \App\Db */
    protected $db;

    /** @var stdClass */
    protected $data;

    public function __construct($database = null) {
        $this->db = ($database) ? $database : new Db();
    }

    public function register($name, $email = null, $password = null) {
        $params = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        // Add the user.
        $userNum = $this->db->query('SELECT COUNT(*) FROM `users` WHERE `name` LIKE :name', ['name' => "$name%"])->fetchColumn();
        $userName = ($userNum > 0) ? $name.' '.($userNum+1) : $name;
        $params['name'] = $userName;
        $this->db->query("INSERT INTO users SET name=:name, email=:email, password=:password", $params);
        $id = $this->db->lastInsertId();
        $this->load($id);

        // Add the new user to a group of their own.
        $groupNum = $this->db->query('SELECT COUNT(*) FROM `groups` WHERE `name` LIKE :name', ['name' => "$name%"])->fetchColumn();
        $groupName = ($groupNum > 0) ? $name.' '.($groupNum+1) : $name;
        $this->db->query('INSERT INTO `groups` SET `name`=:name', ['name' => $groupName]);
        $gid = $this->db->lastInsertId();
        $this->db->query('INSERT INTO `user_groups` SET `user`=:u, `group`=:g', ['u'=>$this->getId(), 'g'=>($gid)]);
    }

    public function getGroupNames() {
        if (!$this->getId()) {
            return [];
        }
        $sql = "SELECT g.name FROM users u "
            . " JOIN user_groups ug ON ug.user = u.id "
            . " JOIN `groups` g ON ug.group = g.id"
            . " WHERE u.id = :id";
        return $this->db->query($sql, ['id' => $this->getId()])->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getReminder() {
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

    public function checkReminderToken($token) {
        return password_verify($token, $this->data->reminder_token);
    }

    public function changePassword($password) {
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

    public function load($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $this->data = $this->db->query($sql, ['id' => $id])->fetch();
    }

    public function loadByName($name) {
        $sql = "SELECT * FROM users WHERE name = :name";
        $this->data = $this->db->query($sql, ['name' => $name])->fetch();
    }

    public function getId() {
        return (isset($this->data->id)) ? (int) $this->data->id : false;
    }

    public function getName() {
        return isset($this->data->name) ? $this->data->name : false;
    }

    public function getEmail() {
        return isset($this->data->email) ? $this->data->email : false;
    }
}
