<?php

namespace App;

class Item {

    /** The default date granularity is 'exact' (ID 1). */
    const DATE_GRANULARITY_DEFAULT = 1;

    /** @var \App\Db */
    private $db;

    /** @var \StdClass */
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

    /**
     * Save an item's data.
     *
     * @param string[] $medatdata Array of metadata pairs.
     * @param string $keywordsString CSV string of keywords.
     * @param string $filename The full filesystem path to a file to attach to this Item.
     * @param string $fileContents A string to treat as the contents of a file.
     */
    public function save($medatdata, $keywordsString = null, $filename = null, $fileContents = null) {
        if (empty($medatdata['title'])) {
            $medatdata['title'] = 'Untitled';
        }
        if (empty($medatdata['description'])) {
            $medatdata['description'] = null;
        }
        if (empty($medatdata['date'])) {
            $medatdata['date'] = null;
        }
        if (empty($medatdata['date_granularity'])) {
            $medatdata['date_granularity'] = self::DATE_GRANULARITY_DEFAULT;
        }
        if (empty($medatdata['auth_level'])) {
            $medatdata['auth_level'] = 0;
        }
        $setClause = 'SET title=:title, description=:description, date=:date, '
            . ' date_granularity=:date_granularity, auth_level=:auth_level ';

        // Start a transaction. End after the key words and files have been written.
        $this->db->query('BEGIN');

        if (isset($medatdata['id']) && is_numeric($medatdata['id'])) {
            // Update?
            $sql = "UPDATE items $setClause WHERE id=:id";
            $this->db->query($sql, $medatdata);
            $id = $medatdata['id'];
        } else {
            // Or insert?
            unset($medatdata['id']);
            $sql = "INSERT INTO items $setClause";
            $this->db->query($sql, $medatdata);
            $id = $this->db->lastInsertId();
        }
        $this->load($id);

        // Save keywords.
        if ($keywordsString) {
            $this->db->query("DELETE FROM item_keywords WHERE item=:id", ['id' => $id]);
            $keywords = array_map('trim', array_unique(str_getcsv($keywordsString)));
            foreach ($keywords as $kwd) {
                $this->db->query("INSERT IGNORE INTO keywords SET title=:title", ['title' => $kwd]);
                $selectKeywordId = "SELECT id FROM keywords WHERE title LIKE :title";
                $keywordId = $this->db->query($selectKeywordId, ['title' => $kwd])->fetchColumn();
                $insertJoin = "INSERT IGNORE INTO item_keywords SET item=:item, keyword=:keyword";
                $this->db->query($insertJoin, ['item' => $id, 'keyword' => $keywordId]);
            }
        }

        if (!empty($fileContents)) {
            $filesystem = App::getFilesystem();
            $newVer = $this->getVersionCount() + 1;
            $filesystem->put("storage://".$this->getFilePath($newVer), $fileContents);
        }

        // Save file.
        if ($filename) {
            $filesystem = App::getFilesystem();
            $stream = fopen($filename, 'r+');
            $filesystem->putStream("storage://".$this->getFilePath(), $stream);
            fclose($stream);
        }

        // End the transaction and reload the data from the DB.
        $this->db->query('COMMIT');
    }

    /**
     * Get the contents of the file.
     *
     * @param integer $version Which file version to get.
     * @return false|string
     * @throws \Exception
     */
    public function getFileContents($version = null)
    {
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        $filesystem = App::getFilesystem();
        $path = "storage://".$this->getFilePath($version);
        if ($filesystem->has($path)) {
            return $filesystem->read($path);
        }
    }

    public function getVersionCount()
    {
        $filesystem = App::getFilesystem();
        $out = $filesystem->getFilesystem('storage')->listContents($this->getHashedPath());
        return count($out);
    }

    public function getHashedPath()
    {
        $hash = md5($this->getId());
        return $hash[0] . $hash[1] . '/' . $hash[2] . $hash[3];
    }

    /**
     * Get the path to a version of the attached file.
     * Never has a leading slash, and the last component is the filename.
     * @return string
     * @throws \Exception
     */
    public function getFilePath($version = null) {
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        if (!is_int($version)) {
            throw new \Exception("Version must be an integer ('$version' was given)");
        }
        return $this->getHashedPath() . '/v' . $version;
    }

    public function getDateGranularities() {
        return $this->db->query("SELECT id, title FROM date_granularities ORDER BY id ASC")->fetchAll();
    }

    public function getKeywords() {
        $keywordSql = 'SELECT k.id, k.title '
            . ' FROM item_keywords ik JOIN keywords k ON (ik.keyword=k.id) '
            . ' WHERE ik.item=:id '
            . ' ORDER BY k.title ASC ';
        $params = ['id' => $this->getId()];
        return $this->db->query($keywordSql, $params)->fetchAll();
    }

    public function getKeywordsString()
    {
        $out = [];
        foreach ($this->getKeywords() as $keyword) {
            $out[] = $keyword->title;
        }
        return join(', ', $out);
    }

    public function getId() {
        return isset($this->data->id) ? (int) $this->data->id : null;
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
