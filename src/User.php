<?php

namespace App;

class User {

    /** @var \App\Db */
    protected $db;

    /** @var stdClass */
    protected $data;

    public function __construct($database) {
        $this->db = $database;
    }

    public function save($name, $password = null) {
        $params = [];
        if ($this->getId()) {
            $sql = "UPDATE users SET name=:name WHERE id=:id";
            $params['id'] = $this->getId();
        } else {
            $sql = "INSERT INTO users SET name=:name";
        }
        if (!empty($password)) {
            $params['password'] = password_hash($password);
        }
        $keepTrying = true;
        $nameNumber = 1;
        $finalName = $name;
        while ($keepTrying) {
            try {
                $params['name'] = $finalName;
                $this->db->query($sql, $params);
                $keepTrying = false;
            } catch (\PDOException $e) {
                // Error: 1022 SQLSTATE: 23000 (ER_DUP_KEY) Message: Can't write; duplicate key in table '%s'
                if ($e->getCode() === '23000') {
                    $nameNumber++;
                    $finalName = $name . " $nameNumber";
                } else {
                    throw $e;
                }
            }
        }
        $id = $this->getId() ? $this->getId() : $this->db->lastInsertId();
        $this->load($id);
    }

    public function load($id) {
        $sql = "SELECT id, name FROM users WHERE id = :id";
        $this->data = $this->db->query($sql, ['id' => $id])->fetch();
    }

    public function getId() {
        return (isset($this->data->id)) ? (int) $this->data->id : false;
    }

    public function getName() {
        return isset($this->data->name) ? $this->data->name : false;
    }

}
