<?php
namespace Aneris\Stdlib;

use IteratorAggregate;
use Countable;

class PriorityQueueForRegacy implements IteratorAggregate, Countable
{
    const EXTR_DATA = 1;
    const EXTR_PRIORITY = 2;
    const EXTR_BOTH = 3;

    private $_need_to_sort;
    private $_sorted_priority;
    private $_queue;
    private $_extractFlags = self::EXTR_DATA;

    public function __construct()
    {
        $this->_need_to_sort = false;
        $this->_queue = array();
    }

    public function setExtractFlags($flags)
    {
        $this->_extractFlags = $flags;
    }

    public function insert( $value , $priority )
    {
        if(!isset($this->_queue[$priority])) {
            $this->_queue[$priority] = array();
            $this->_need_to_sort = true;
        }
        $this->_queue[$priority][] = $value;
    }

    public function isEmpty()
    {
        return count($this->_queue) ? false : true;
    }

    public function count()
    {
        $count = 0;
        foreach($this->_queue as $queue) {
            $count += count($queue);
        }
        return $count;
    }

    private function get_first_priority()
    {
        if($this->_need_to_sort) {
            $this->_sorted_priority = array_keys($this->_queue);
            rsort($this->_sorted_priority);
            $this->_need_to_sort = false;
        }
        return $this->_sorted_priority[0];
    }

    public function remove($datum)
    {
        foreach($this->_queue as $priority => $queue) {
            foreach($queue as $key => $value) {
                if($value === $datum) {
                    unset($this->_queue[$priority][$key]);
                    return true;
                }
            }
        }
        return false;
    }

    public function extract()
    {
        if($this->isEmpty())
            return null;
        $priority = $this->get_first_priority();
        $value = array_shift($this->_queue[$priority]);
        if(count($this->_queue[$priority])==0) {
            unset($this->_queue[$priority]);
            array_shift($this->_sorted_priority);
        }
        return $value;
    }

    public function getIterator()
    {
        if($this->isEmpty())
            return array();
        $this->get_first_priority();
        $ar = array();
        switch ($this->_extractFlags & 0x03) {
            case self::EXTR_DATA:
                foreach($this->_sorted_priority as $priority) {
                    $ar = array_merge($ar, $this->_queue[$priority]);
                }
                break;
            case self::EXTR_PRIORITY:
                foreach($this->_sorted_priority as $priority) {
                    foreach ($this->_queue[$priority] as $value) {
                        $ar[] = $priority;
                    }
                }
                break;
            case self::EXTR_BOTH:
                foreach($this->_sorted_priority as $priority) {
                    foreach ($this->_queue[$priority] as $value) {
                        $ar[] = array('data' => $value, 'priority' => $priority);
                    }
                }
                break;
            default:
                break;
        }
        return new \ArrayIterator($ar);
    }
}
