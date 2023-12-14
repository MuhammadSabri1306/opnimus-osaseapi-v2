<?php
namespace App\Core\Collections;

class Collection
{
    private $data = [];

    public function __construct(array $data = [])
    {
        if($data != array_values($data)) {
            $this->data = $data;
        }
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function unset($key)
    {
        unset($this->data[$key]);
    }

    public function toArray()
    {
        return $this->data;
    }
}