<?php
namespace Aneris\Stdlib;

use IteratorAggregate;
use Countable;
use SplPriorityQueue;
use Serializable;

class PriorityQueue implements IteratorAggregate, Countable, Serializable
{
	const EXTR_DATA     = SplPriorityQueue::EXTR_DATA;
	const EXTR_PRIORITY = SplPriorityQueue::EXTR_PRIORITY;
	const EXTR_BOTH     = SplPriorityQueue::EXTR_BOTH;	

	private $queue;
	private $extractFlags=self::EXTR_DATA;

	public function __construct() {
		$this->queue = new SplPriorityQueue();
	}

    public function __clone()
    {
        $this->queue = clone $this->queue;
    }

    public function insert( $value , $priority )
    {
    	$this->queue->insert($value , $priority);
    }

    public function isEmpty()
    {
    	return $this->queue->isEmpty();
    }

    public function count()
    {
    	return $this->queue->count();
    }

    public function remove($datum)
    {
        return false;
    }

    public function merge(PriorityQueue $queue)
    {
        $que = clone $queue;
    	$que->setExtractFlags(self::EXTR_BOTH);
    	while($que->valid()) {
    		$value = $que->extract();
    		$this->queue->insert($value['data'],$value['priority']);
    	}
    	return $this;
    }

    public function extract()
    {
    	return $this->queue->extract();
    }

    public function valid()
    {
    	return $this->queue->valid();
    }

    public function recoverFromCorruption()
    {
    	$this->queue->recoverFromCorruption();
    }

    public function setExtractFlags($flags)
    {
    	$this->queue->setExtractFlags($flags);
    	$this->extractFlags=$flags;
    }

    public function getExtractFlags()
    {
    	return $this->extractFlags;
    }

    public function getIterator()
    {
    	return clone $this->queue;
    }

    public function serialize()
    {
        $array = array();
        $queue = clone $this->queue;
        $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
    	foreach ($queue as $set) {
    		$array[] = $set;
    	}
    	return serialize($array);
    }

    public function unserialize($data)
    {
    	$array = unserialize($data);
    	$this->queue = new SplPriorityQueue();
    	foreach ($array as $set) {
    		$this->queue->insert($set['data'],$set['priority']);
    	}
    }
}