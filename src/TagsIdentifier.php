<?php

namespace App;

/**
 * A wrapper around comma-delimited strings of tag IDs. This class is used to make sure the IDs
 * are always used in the same order, and to make it easier to add and remove tags.
 */
class TagsIdentifier {

    /** @var integer[] Tag IDs. */
    protected $tags;

    /**
     * TagsIdentifier constructor.
     */
    public function __construct()
    {
        $this->tags = [];
    }

    /**
     * @param string $id A numeric string.
     * @return $this
     */
    public function add($id)
    {
        if (is_numeric($id)) {
            if (!$this->contains($id)) {
                $this->tags[] = $id;
                sort($this->tags);
            }
        }
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function addFromString($str)
    {
        foreach (explode(',', $str) as $tag) {
            $this->add($tag);
        }
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function remove($id)
    {
        foreach ($this->tags as $idx => $tag) {
            if ($tag == $id) {
                unset($this->tags[$idx]);
                return $this;
            }
        }
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function removeFromString($str)
    {
        foreach (explode(',', $str) as $tag) {
            $this->remove($tag);
        }
        return $this;
    }

    public function isEmpty()
    {
        return count($this->tags) === 0;
    }

    public function contains($id)
    {
        return in_array($id, $this->tags);
    }

    public function toString($with = false, $without = false)
    {
        if ($with) {
            $this->add($with);
        }
        if ($without) {
            $this->remove($without);
        }
        $str = join(',', $this->tags);
        if ($with) {
            $this->remove($with);
        }
        if ($without) {
            $this->add($without);
        }
        return $str;
    }

    public function toArray()
    {
        return $this->tags;
    }
}