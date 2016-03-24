<?php

namespace App;

use Intervention\Image\ImageManager;

class Item
{

    /** The default date granularity is 'exact' (ID 1). */
    const DATE_GRANULARITY_DEFAULT = 1;

    /** @var \App\Db */
    private $db;

    /** @var \StdClass */
    private $data;

    /** @var User */
    protected $user;

    public function __construct($id = null, User $user = null)
    {
        $this->db = new Db();
        if ($id !== null) {
            $this->load($id);
        }
        $this->user = $user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function load($id)
    {
        if (!empty($id) && !is_numeric($id)) {
            throw new \Exception("Not an Item ID: " . print_r($id, true));
        }
        $sql = 'SELECT items.id, items.title, items.description, items.date, '
            . '    items.date_granularity, dg.php_format AS date_granularity_format, '
            . '    items.read_group, items.edit_group '
            . ' FROM items JOIN date_granularities dg ON dg.id=items.date_granularity '
            . ' WHERE items.id=:id ';
        $params = ['id' => $id];
        $this->data = $this->db->query($sql, $params)->fetch();
    }

    /**
     * Is this item editable by any of the current user's groups?
     *
     * @return bool
     */
    public function editable()
    {
        if (!$this->user || !$this->user->getId()) {
            return false;
        }
        if (!$this->getId()) {
            return true;
        }
        $editGroupId = $this->getEditGroup()->id;
        foreach ($this->user->getGroups() as $group) {
            if ($editGroupId == $group['id']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save an item's data.
     *
     * @param string[] $metadata Array of metadata pairs.
     * @param string $tagsString CSV string of tags.
     * @param string $filename The full filesystem path to a file to attach to this Item.
     * @param string $fileContents A string to treat as the contents of a file.
     * @return false
     */
    public function save($metadata, $tagsString = null, $filename = null, $fileContents = null)
    {
        if (isset($metadata['id'])) {
            $this->load($metadata['id']);
        }
        if (!$this->editable()) {
            throw new \Exception("You are not allowed to edit this item.");
        }
        if (empty($metadata['title'])) {
            $metadata['title'] = 'Untitled';
        }
        if (empty($metadata['description'])) {
            $metadata['description'] = null;
        }
        if (empty($metadata['date'])) {
            $metadata['date'] = null;
        }
        if (empty($metadata['date_granularity'])) {
            $metadata['date_granularity'] = self::DATE_GRANULARITY_DEFAULT;
        }
        if (empty($metadata['edit_group'])) {
            $metadata['edit_group'] = $this->getEditGroup()->id;
        }
        if (empty($metadata['read_group'])) {
            $metadata['read_group'] = $this->getReadGroup()->id;
        }
        $setClause = 'SET title=:title, description=:description, date=:date, '
            . ' date_granularity=:date_granularity, edit_group=:edit_group, read_group=:read_group ';

        // Start a transaction. End after the key words and files have been written.
        $this->db->query('BEGIN');

        if (isset($metadata['id']) && is_numeric($metadata['id'])) {
            // Update?
            $sql = "UPDATE items $setClause WHERE id=:id";
            $this->db->query($sql, $metadata);
            $id = $metadata['id'];
        } else {
            // Or insert?
            unset($metadata['id']);
            $sql = "INSERT INTO items $setClause";
            $this->db->query($sql, $metadata);
            $id = $this->db->lastInsertId();
        }
        $this->load($id);

        // Save tags.
        if (!empty($tagsString)) {
            $this->db->query("DELETE FROM item_tags WHERE item=:id", ['id' => $id]);
            $tags = array_map('trim', array_unique(str_getcsv($tagsString)));
            foreach ($tags as $tag) {
                $this->db->query("INSERT IGNORE INTO tags SET title=:title", ['title' => $tag]);
                $selectTagId = "SELECT id FROM tags WHERE title LIKE :title";
                $tagId = $this->db->query($selectTagId, ['title' => $tag])->fetchColumn();
                $insertJoin = "INSERT IGNORE INTO item_tags SET item=:item, tag=:tag";
                $this->db->query($insertJoin, ['item' => $id, 'tag' => $tagId]);
            }
        }

        $newVer = $this->getVersionCount() + 1;
        // Save file contents.
        if (!empty($fileContents)) {
            $filesystem = App::getFilesystem();
            $filesystem->put("storage://" . $this->getFilePath($newVer), $fileContents);
        }

        // Save uploaded file.
        if (!empty($filename)) {
            $filesystem = App::getFilesystem();
            $stream = fopen($filename, 'r+');
            $filesystem->putStream("storage://" . $this->getFilePath($newVer), $stream);
            fclose($stream);
        }

        // End the transaction and reload the data from the DB.
        $this->db->query('COMMIT');
    }

    /**
     * Get the file's mime type, or false if there's no file.
     * @param integer $version
     * @return integer|false
     */
    public function getMimeType($version = null)
    {
        if (!$this->getId()) {
            return false;
        }
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        $filesystem = App::getFilesystem();
        $path = "storage://" . $this->getFilePath($version);
        if ($filesystem->has($path)) {
            return $filesystem->getMimetype($path);
        }
        return false;
    }

    /**
     * Whether this file is a text file.
     */
    public function isText($version = null)
    {
        return $this->getMimeType($version) === 'text/plain';
    }

    public function isImage($version = null)
    {
        return 0 === strpos($this->getMimeType($version), 'image');
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
        if (!$this->getId()) {
            return false;
        }
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        $filesystem = App::getFilesystem();
        $path = "storage://" . $this->getFilePath($version);
        if ($filesystem->has($path)) {
            return $filesystem->read($path);
        }
        return false;
    }

    /**
     * Get a local filesystem path to the requested file.
     *
     * @param string $format
     * @param null $version
     * @throws \Exception
     */
    public function getCachePath($format = 'o', $version = null)
    {
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        $filesystem = App::getFilesystem();
        $path = $this->getFilePath($version);

        // Get local filesystem root.
        $config = new Config();
        $filesystems = $config->filesystems();
        $root = realpath($filesystems['cache']['root']);

        // First of all copy the original file to the cache.
        $filenameOrig = $this->getId() . '_v' . $version . '_o';
        if (!$filesystem->has("cache://" . $filenameOrig)) {
            $filesystem->copy("storage://$path", "cache://" . $filenameOrig);
        }
        $pathnameOrig = $root . DIRECTORY_SEPARATOR . $filenameOrig;
        if ($format === 'o') {
            return $pathnameOrig;
        }

        // Then create smaller version if required.
        $filenameDisplay = $this->getId() . '_v' . $version . '_t';
        $pathnameDisplay = $root . DIRECTORY_SEPARATOR . $filenameDisplay;
        $manager = new ImageManager();
        $image = $manager->make($pathnameOrig);
        $image->fit(200);
        $image->save($pathnameDisplay);

        clearstatcache(false, $pathnameDisplay);

        return $pathnameDisplay;
    }

    public function getFileStream($version = null)
    {
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        $filesystem = App::getFilesystem();
        $path = "storage://" . $this->getFilePath($version);
        if ($filesystem->has($path)) {
            return $filesystem->readStream($path);
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
        return $hash[0] . $hash[1] . '/' . $hash[2] . $hash[3] . '/' . $this->getId();
    }

    /**
     * Get the path to a version of the attached file.
     * Never has a leading slash, and the last component is the filename.
     * @return string
     * @throws \Exception
     */
    public function getFilePath($version = null)
    {
        if (is_null($version)) {
            $version = $this->getVersionCount();
        }
        if (!is_int($version)) {
            throw new \Exception("Version must be an integer ('$version' was given)");
        }
        return $this->getHashedPath() . '/v' . $version;
    }

    public function getTags()
    {
        $tagsSql = 'SELECT t.id, t.title '
            . ' FROM item_tags it JOIN tags t ON (it.tag=t.id) '
            . ' WHERE it.item=:id '
            . ' ORDER BY t.title ASC ';
        $params = ['id' => $this->getId()];
        return $this->db->query($tagsSql, $params)->fetchAll();
    }

    public function getTagsString()
    {
        $out = [];
        foreach ($this->getTags() as $tag) {
            $out[] = $tag->title;
        }
        return join(', ', $out);
    }

    public function getId()
    {
        return isset($this->data->id) ? (int) $this->data->id : false;
    }

    public function getTitle()
    {
        return isset($this->data->title) ? $this->data->title : false;
    }

    public function getDescription()
    {
        return isset($this->data->description) ? $this->data->description : false;
    }

    public function getDate()
    {
        return isset($this->data->date) ? $this->data->date : false;
    }

    public function getEditGroup()
    {
        $defaultGroup = ($this->user instanceof User) ? $this->user->getDefaultGroup()->id : User::GROUP_ADMIN;
        $groupId = isset($this->data->edit_group) ? $this->data->edit_group : $defaultGroup;
        return $this->db->query("SELECT * FROM groups WHERE id=:id", ['id'=>$groupId])->fetch();
    }

    public function getReadGroup()
    {
        $groupId = isset($this->data->read_group) ? $this->data->read_group : User::GROUP_PUBLIC;
        $readGroup = $this->db->query("SELECT * FROM groups WHERE id=:id", ['id'=>$groupId])->fetch();
        //dump($readGroup);exit();
        return $readGroup;
    }

    public function getDateFormatted()
    {
        if (empty($this->data->date)) {
            return '';
        }
        $format = $this->data->date_granularity_format;
        $date = new \DateTime($this->data->date);
        return $date->format($format);
    }

    public function getDateGranularity()
    {
        return isset($this->data->date_granularity) ? $this->data->date_granularity : self::DATE_GRANULARITY_DEFAULT;
    }
}
