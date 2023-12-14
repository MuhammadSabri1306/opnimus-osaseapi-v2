<?php
namespace App\Core\Collections;

use App\Core\Collections\Collection;
use App\Core\Collections\ListIteratorStopException;

class CollectionList
{
    private $collections = [];

    public function __construct($collections = [])
    {
        if($collections instanceof Collection) {
            array_push($this->collections, $collections);
        }
        
        foreach($collections as $item) {
            array_push($this->collections, new Collection($item));
        }
    }

    public function isEmpty(): bool
    {
        return $this->count() < 1;
    }

    public function count(callable $checker = null): int
    {
        if(is_null($checker)) {
            return count($this->collections);
        }
        
        $count = 0;
        $this->each(function($item) use ($checker, &$count) {
            if($checker($item)) $count++;
        });
        return $count;
    }

    public function push($collection)
    {
        if(!$collection instanceof Collection) {
            $collection = new Collection($collection);
        }
        array_push($this->collections, $collection);
    }
    
    public function map(callable $formatter)
    {
        $collections = [];
        try {
            foreach($this->collections as $index => $collection) {
                array_push($collections, $formatter($collection, $index));
            }
        } catch(ListIteratorStopException $e) {}
        return $collections;
    }

    public function each(callable $callback)
    {
        try {
            foreach($this->collections as $index => $collection) {
                $callback($collection, $index);
            }
        } catch(ListIteratorStopException $e) {}
    }

    public function find(callable $checker): Collection
    {
        $collection = null;
        $this->each(function($item) use(&$collection, $checker) {
            if($checker($item)) {
                $collection = $item;
                CollectionList::stopIteration();
            }
        });
        return $collection;
    }

    public function findIndex(callable $checker): Collection
    {
        $collectionIndex = null;
        $this->each(function($item, $index) use(&$collection, $collectionIndex, $checker) {
            if($checker($item)) {
                $collectionIndex = $index;
                CollectionList::stopIteration();
            }
        });
        return $collectionIndex;
    }

    public function toArray()
    {
        return $this->map(fn($collection) => $collection->toArray());
    }

    public static function stopIteration()
    {
        throw new ListIteratorStopException('List iteration is stopped');
    }
}