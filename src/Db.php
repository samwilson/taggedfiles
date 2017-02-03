<?php

namespace App;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Db
{

    /** @var PDO */
    static protected $pdo;

    /** @var string[] */
    static protected $queries = [];

    public function __construct()
    {
        if (self::$pdo) {
            return;
        }
        $config = require CONFIG_FILE;
        $dbConfig = $config['database'];
        $host = isset($dbConfig['host']) ? $dbConfig['host'] : 'localhost';
        $dsn = "mysql:host=$host;dbname=" . $dbConfig['database'];
        $attr = array(PDO::ATTR_TIMEOUT => 10);
        self::$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $attr);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setFetchMode(PDO::FETCH_OBJ);
    }

    public static function getQueries()
    {
        return self::$queries;
    }

    /**
     * Wrapper for \PDO::lastInsertId().
     * @return string
     */
    public function lastInsertId()
    {
        return self::$pdo->lastInsertId();
    }

    public function setFetchMode($fetchMode)
    {
        return self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMode);
    }

    /**
     * Get a result statement for a given query. Handles errors.
     *
     * @param string $sql The SQL statement to execute.
     * @param array $params Array of param => value pairs.
     * @param string $class The PHP class of each item of the result set.
     * @param mixed $classArgs The arguments to pass to the constructor of the class.
     * @return PDOStatement Resulting PDOStatement.
     * @throws Exception If the requested result class does not exist.
     */
    public function query($sql, $params = null, $class = null, $classArgs = null)
    {
        if (!empty($class) && !class_exists($class)) {
            throw new Exception("Class not found: $class");
        }
        if (is_array($params) && count($params) > 0) {
            $stmt = self::$pdo->prepare($sql);
            foreach ($params as $placeholder => $value) {
                if (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                } elseif (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $stmt->bindValue($placeholder, $value, $type);
            }
            if ($class !== null) {
                $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $class, $classArgs);
            } else {
                $stmt->setFetchMode(PDO::FETCH_OBJ);
            }
            $result = $stmt->execute();
            if (!$result) {
                throw new PDOException('Unable to execute parameterised SQL: <code>' . $sql . '</code>');
            }
        } else {
            try {
                if ($class !== null) {
                    $stmt = self::$pdo->query($sql, PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $class, $classArgs);
                } else {
                    $stmt = self::$pdo->query($sql);
                }
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage() . ' -- Unable to execute SQL: <code>' . $sql . '</code>');
            }
        }

        self::$queries[] = $sql;
        return $stmt;
    }

    public function install()
    {
        $this->query("CREATE TABLE IF NOT EXISTS groups ("
            . " id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
            . " name VARCHAR(200) NOT NULL UNIQUE"
            . ")");
        $this->query("INSERT IGNORE INTO groups (`id`, `name`) VALUES "
            . "(" . User::GROUP_PUBLIC . ", 'Public'), "
            . "(" . User::GROUP_ADMIN . ", 'Administrators')");
        $this->query("CREATE TABLE IF NOT EXISTS date_granularities ("
            . " id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
            . " title VARCHAR(20) NOT NULL UNIQUE,"
            . " php_format VARCHAR(50) NOT NULL UNIQUE"
            . ")");
        $granularities = [
             [1, 'Exact', 'Y-m-d H:i:s'],
             [2, 'Day', 'j F Y'],
             [3, 'Month', 'F Y'],
             [4, 'Year', 'Y'],
             [5, 'Circa', '\\c. Y'],
        ];
        foreach ($granularities as $gran) {
            $this->query(
                "INSERT IGNORE INTO date_granularities SET id=:id, title=:t, php_format=:f",
                ['id' => $gran[0], 't' => $gran[1], 'f' => $gran[2]]
            );
        }
        $this->query("CREATE TABLE IF NOT EXISTS items ("
            . " id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
            . " title VARCHAR(100) NOT NULL UNIQUE,"
            . " date DATETIME NULL DEFAULT NULL,"
            . " date_granularity INT(2) UNSIGNED NOT NULL DEFAULT 1,"
            . "     FOREIGN KEY (date_granularity) REFERENCES date_granularities (id),"
            . " description TEXT NULL DEFAULT NULL,"
            . " read_group INT(5) UNSIGNED NOT NULL DEFAULT " . User::GROUP_PUBLIC . ","
            . "     FOREIGN KEY (read_group) REFERENCES groups (id),"
            . " edit_group INT(5) UNSIGNED NOT NULL,"
            . "     FOREIGN KEY (edit_group) REFERENCES groups (id)"
            . ")");
        $this->query("CREATE TABLE IF NOT EXISTS tags ("
            . " id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
            . " title VARCHAR(200) NOT NULL UNIQUE"
            . ")");
        $this->query("CREATE TABLE IF NOT EXISTS item_tags ("
            . " item INT(10) UNSIGNED NOT NULL,"
            . "     FOREIGN KEY (item) REFERENCES items (id),"
            . " tag INT(5) UNSIGNED NOT NULL,"
            . "     FOREIGN KEY (tag) REFERENCES tags (id),"
            . " PRIMARY KEY (item, tag)"
            . ")");
        $this->query("CREATE TABLE IF NOT EXISTS `users` ("
            . " `id` INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
            . " `name` VARCHAR(200) NOT NULL UNIQUE,"
            . " `email` VARCHAR(200) NULL DEFAULT NULL,"
            . " `password` VARCHAR(255) NULL DEFAULT NULL,"
            . " `reminder_token` VARCHAR(255) NULL DEFAULT NULL,"
            . " `reminder_time` DATETIME NULL DEFAULT NULL,"
            . " `default_group` INT(5) UNSIGNED NOT NULL DEFAULT ".User::GROUP_PUBLIC.","
            . "     FOREIGN KEY (`default_group`) REFERENCES `groups` (`id`)"
            . ")");
        $this->query("CREATE TABLE IF NOT EXISTS `user_groups` ("
            . " `user` INT(5) UNSIGNED NOT NULL,"
            . "     FOREIGN KEY (`user`) REFERENCES `users` (`id`),"
            . " `group` INT(5) UNSIGNED NOT NULL,"
            . "     FOREIGN KEY (`group`) REFERENCES `groups` (`id`),"
            . " PRIMARY KEY (`user`, `group`)"
            . ")");
        $this->query("INSERT IGNORE INTO `user_groups` (`user`, `group`) "
            . " SELECT id," . User::GROUP_PUBLIC . " FROM users");
    }
}
