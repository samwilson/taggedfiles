<?php

namespace App;

class Item {

    /** The default date granularity is 'exact' (ID 1). */
    const DATE_GRANULARITY_DEFAULT = 1;

    /** @var \App\Db */
    private $db;

    /** @var string[] */
    private $data;

    public function __construct($id = null) {
        $this->db = new Db();
        if ($id !== null) {
            $this->load($id);
        }
    }

    public function load($id) {
        if (!empty($id) && !is_numeric($id)) {
            throw new \Exception("Not an Item ID: " . print_r($id, true));
        }
        $sql = 'SELECT items.id, items.title, items.description, items.date, '
            . '    items.date_granularity, dg.php_format AS date_granularity_format '
            . ' FROM items JOIN date_granularities dg ON dg.id=items.date_granularity '
            . ' WHERE items.id=:id ';
        $params = ['id' => $id];
        $this->data = $this->db->query($sql, $params)->fetch();
    }

    public function save($data) {
        if (empty($data['title'])) {
            $data['title'] = 'Untitled';
        }
        if (empty($data['description'])) {
            $data['description'] = null;
        }
        if (empty($data['date'])) {
            $data['date'] = null;
        }
        if (empty($data['date_granularity'])) {
            $data['date_granularity'] = self::DATE_GRANULARITY_DEFAULT;
        }
        if (empty($data['auth_level'])) {
            $data['auth_level'] = 0;
        }
        $setClause = 'SET title=:title, description=:description, date=:date, '
            . ' date_granularity=:date_granularity, auth_level=:auth_level ';
        $this->db->query('BEGIN');
        if (isset($data['id']) && is_numeric($data['id'])) {
            $sql = "UPDATE items $setClause WHERE id=:id";
            $this->db->query($sql, $data);
            $id = $data['id'];
        } else {
            unset($data['id']);
            $sql = "INSERT INTO items $setClause";
            $this->db->query($sql, $data);
            $id = $this->db->lastInsertId();
        }
        $this->db->query('COMMIT');
        $this->load($id);
    }

    public function getDateGranularities() {
        return $this->db->query("SELECT id, title FROM date_granularities ORDER BY id ASC")->fetchAll();
    }

    public function getKeywords() {
        $keywordSql = 'SELECT k.id, k.title '
            . ' FROM item_keywords ik JOIN keywords k ON (ik.keyword=k.id) '
            . ' WHERE ik.item=:id';
        $params = ['id' => $this->getId()];
        return $this->db->query($keywordSql, $params)->fetchAll();
    }

    public function getKeywordsString() {
        $out = [];
        foreach ($this->getKeywords() as $keyword) {
            $out[] = $keyword->title;
        }
        return join(', ', $out);
    }

    public function getId() {
        return isset($this->data->id) ? $this->data->id : null;
    }

    public function getTitle() {
        return isset($this->data->title) ? $this->data->title : null;
    }

    public function getDescription() {
        return isset($this->data->description) ? $this->data->description : null;
    }

    public function getDate() {
        return isset($this->data->date) ? $this->data->date : null;
    }

    public function getDateFormatted() {
        if (empty($this->data->date)) {
            return '';
        }
        $format = $this->data->date_granularity_format;
        $date = new \DateTime($this->data->date);
        return $date->format($format);
    }

    public function getDateGranularity() {
        return isset($this->data->date_granularity) ? $this->data->date_granularity : self::DATE_GRANULARITY_DEFAULT;
    }

}
