<?php

namespace App;

class Db {

    /** @var \PDO */
    static protected $pdo;

    /** @var array|string */
    static protected $queries;

    public function __construct() {
        if (self::$pdo) {
            return;
        }
        $host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
        $dsn = "mysql:host=$host;dbname=" . getenv('DB_NAME');
        $attr = array(\PDO::ATTR_TIMEOUT => 10);
        self::$pdo = new \PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), $attr);
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setFetchMode(\PDO::FETCH_OBJ);
    }

    public static function getQueries() {
        return self::$queries;
    }

    /**
     * Wrapper for \PDO::lastInsertId().
     * @return string
     */
    public function lastInsertId() {
        return self::$pdo->lastInsertId();
    }

    public function setFetchMode($fetchMode) {
        return self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMode);
    }

    /**
     * Get a result statement for a given query. Handles errors.
     *
     * @param string $sql The SQL statement to execute.
     * @param array $params Array of param => value pairs.
     * @return \PDOStatement Resulting PDOStatement.
     */
    public function query($sql, $params = null, $class = null, $classArgs = null) {
        if (!empty($class) && !class_exists($class)) {
            throw new \Exception("Class not found: $class");
        }
        if (is_array($params) && count($params) > 0) {
            $stmt = self::$pdo->prepare($sql);
            foreach ($params as $placeholder => $value) {
                if (is_bool($value)) {
                    $type = \PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = \PDO::PARAM_NULL;
                } elseif (is_int($value)) {
                    $type = \PDO::PARAM_INT;
                } else {
                    $type = \PDO::PARAM_STR;
                }
                $stmt->bindValue($placeholder, $value, $type);
            }
            if ($class) {
                $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class, $classArgs);
            } else {
                $stmt->setFetchMode(\PDO::FETCH_OBJ);
            }
            $result = $stmt->execute();
            if (!$result) {
                throw new \PDOException('Unable to execute parameterised SQL: <code>' . $sql . '</code>');
            }
        } else {
            try {
                if ($class) {
                    $stmt = self::$pdo->query($sql, \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class, $classArgs);
                } else {
                    $stmt = self::$pdo->query($sql);
                }
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage() . ' -- Unable to execute SQL: <code>' . $sql . '</code>');
            }
        }

        self::$queries[] = $sql;
        return $stmt;
    }

    public function install() {
        $this->query("CREATE TABLE IF NOT EXISTS date_granularities ("
                . " id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(20) NOT NULL UNIQUE,"
                . " php_format VARCHAR(50) NOT NULL UNIQUE"
                . ")");
        $this->query("INSERT IGNORE INTO date_granularities (id,title,php_format) VALUES"
                . " (1, 'Exact', 'Y-m-d H:i:s'),"
                . " (2, 'Day', 'j F Y'),"
                . " (3, 'Month', 'F Y'),"
                . " (4, 'Year', 'Y'),"
                . " (5, 'Circa', '\\c. Y')");
        $this->query("CREATE TABLE IF NOT EXISTS items ("
                . " id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(100) NOT NULL UNIQUE,"
                . " date DATE NULL DEFAULT NULL,"
                . " date_granularity INT(2) UNSIGNED NOT NULL DEFAULT 1,"
                . "     FOREIGN KEY (date_granularity) REFERENCES date_granularities (id),"
                . " description TEXT NULL DEFAULT NULL,"
                . " auth_level INT(2) UNSIGNED NOT NULL DEFAULT 0"
                . ")");
        $this->query("CREATE TABLE IF NOT EXISTS keywords ("
                . " id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(200) NOT NULL UNIQUE"
                . ")");
        $this->query("CREATE TABLE IF NOT EXISTS item_keywords ("
                . " item INT(10) UNSIGNED NOT NULL,"
                . "     FOREIGN KEY (item) REFERENCES items (id),"
                . " keyword INT(5) UNSIGNED NOT NULL,"
                . "     FOREIGN KEY (keyword) REFERENCES keywords (id),"
                . " PRIMARY KEY (item, keyword)"
                . ")");
        if (!file_exists(\App\App::datadir())) {
            mkdir(\App\App::datadir(), 0600, true);
        }
    }

}
