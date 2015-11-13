<?php

namespace App;

class Item {

    /** @var \App\Db */
    private $db;

    /** @var string[] */
    private $data;

    public function __construct($db, $id = null) {
        $this->db = $db;
        $sql = 'SELECT items.id, items.title, items.date, dg.php_format AS date_granularity_format '
            . ' FROM items JOIN date_granularities dg ON dg.id=items.date_granularity '
            . ' WHERE items.id=:id ';
        $params = ['id' => $id];
        $this->data = $this->db->query($sql, $params)->fetch();
    }

    public function getKeywords() {
        $keywordSql = 'SELECT k.id, k.title '
                . ' FROM item_keywords ik JOIN keywords k ON (ik.keyword=k.id) '
                . ' WHERE ik.item=:id';
        $params = ['id' => $this->get('id')];
        return $this->db->query($keywordSql, $params)->fetchAll();
    }

    public function get($attr) {
        return $this->data->$attr;
    }

    public function id() {
        return $this->data->id;
    }

    public function dateFormatted() {
        $format = $this->data->date_granularity_format;
        $date = new \DateTime($this->data->date);
        return $date->format($format);
    }
}
