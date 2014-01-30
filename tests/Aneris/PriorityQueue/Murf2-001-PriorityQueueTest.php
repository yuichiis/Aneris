<?php
namespace AnerisTest\PriorityQueueTest;

use stdClass;

// Test Target Class
use Aneris\Stdlib\PriorityQueue;

class PriorityQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testMethods()
    {
        $queue = new PriorityQueue();

        $this->assertEquals(0, $queue->count());

        $queue->insert('First10',  10);
        $this->assertEquals(1, $queue->count());
        $queue->insert('Second10', 10);
        $this->assertEquals(2, $queue->count());
        $queue->insert('First30',  30);
        $this->assertEquals(3, $queue->count());
        $queue->insert('First5',    5);
        $this->assertEquals(4, $queue->count());

        $this->assertEquals('First30',  $queue->extract());
        $this->assertEquals(3, $queue->count());
        $result = $queue->extract();
        $this->assertTrue(($result==='First10'||$result==='Second10'));
        $this->assertEquals(2, $queue->count());
        $result = $queue->extract();
        $this->assertTrue(($result==='First10'||$result==='Second10'));
        $this->assertEquals(1, $queue->count());
        $this->assertEquals('First5',   $queue->extract());
        $this->assertEquals(0, $queue->count());

    }

    public function testForeach()
    {
        $queue = new PriorityQueue();
        $queue->insert('First10',  10);
        $queue->insert('Second10', 10);
        $queue->insert('First30',  30);
        $queue->insert('First5',    5);
        $this->assertEquals(4, $queue->count());

        $idx = 0;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case 0:
                $this->assertEquals('First30',  $data);
                break;
            case 1:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 2:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 3:
                $this->assertEquals('First5',   $data);
                break;
            }
            $idx++;
        }
        $this->assertEquals(4, $queue->count());
        $idx = 0;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case 0:
                $this->assertEquals('First30',  $data);
                break;
            case 1:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 2:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 3:
                $this->assertEquals('First5',   $data);
                break;
            }
            $idx++;
        }

        $queue->insert('First40',  40);
        $this->assertEquals(5, $queue->count());
        $idx = -1;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case -1:
                $this->assertEquals('First40',  $data);
                break;
            case 0:
                $this->assertEquals('First30',  $data);
                break;
            case 1:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 2:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 3:
                $this->assertEquals('First5',   $data);
                break;
            }
            $idx++;
        }

        $queue->insert('First4',  4);
        $this->assertEquals(6, $queue->count());
        $idx = -1;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case -1:
                $this->assertEquals('First40',  $data);
                break;
            case 0:
                $this->assertEquals('First30',  $data);
                break;
            case 1:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 2:
                $this->assertTrue(($data==='First10'||$data==='Second10'));
                break;
            case 3:
                $this->assertEquals('First5',   $data);
                break;
            case 4:
                $this->assertEquals('First4',   $data);
                break;
            }
            $idx++;
        }
    }
/*
    public function testRemove()
    {
        $object1 = new stdClass();
        $object1->abc = 1;
        $object2 = new stdClass();
        $object2->abc = 1;

        $this->assertFalse(($object1 === $object2));

        $queue = new PriorityQueue();
        $queue->insert('First10',  10);
        $queue->insert($object1,   10);
        $queue->insert('First30',  30);
        $queue->insert('Second30', 30);
        $queue->insert($object2, 10);
        $queue->insert('First5',    5);
        $this->assertEquals(6, $queue->count());

        $queue->remove('Second30');
        $this->assertEquals(5, $queue->count());
        $queue->remove($object1);
        $this->assertEquals(4, $queue->count());

        $idx = 0;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case 0:
                $this->assertEquals('First30',  $data);
                break;
            case 1:
                $this->assertEquals('First10',  $data);
                break;
            case 2:
                $this->assertFalse(($object1 === $data));
                $this->assertTrue(($object2 === $data));
                break;
            case 3:
                $this->assertEquals('First5',   $data);
                break;
            }
            $idx++;
        }
        $this->assertEquals(4, $queue->count());
    }
*/
    public function testIterateMultiple()
    {
        $queue = new PriorityQueue();
        $queue->insert('First10',  10);
        $queue->insert('First11',  10);
        $queue->insert('First5',   5);
        $queue->insert('First6',   5);

        $idx = 0;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case 0:
                $this->assertEquals('First10',  $data);
                break;
            case 1:
                $this->assertEquals('First11',  $data);
                break;
            case 2:
                $this->assertEquals('First5',   $data);
                break;
            case 3:
                $this->assertEquals('First6',   $data);
                break;
            }
            $idx++;
        }

        $idx = 0;
        foreach($queue as $priority => $data) {
            switch($idx) {
            case 0:
                $this->assertEquals('First10',  $data);
                break;
            case 1:
                $this->assertEquals('First11',  $data);
                break;
            case 2:
                $this->assertEquals('First5',   $data);
                break;
            case 3:
                $this->assertEquals('First6',   $data);
                break;
            }
            $idx++;
        }
    }

    public function testClone()
    {
        $queue = new PriorityQueue();
        $queue->insert('First10',  10);
        $queue2 = clone $queue;

        $this->assertEquals(1,count($queue));
        $queue->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue->extract());

        $this->assertEquals(1,count($queue2));
        $queue2->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue2->extract());
    }

    public function testSerialize()
    {
        $queue = new PriorityQueue();
        $queue->insert('First10',  10);
        $data = serialize($queue);
        $this->assertEquals(1,count($queue));
        $queue->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue->extract());

        $queue = unserialize($data);
        $queue->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue->extract());
    }

    public function testMerge()
    {
        $queue1 = new PriorityQueue();
        $queue1->insert('First30',  30);
        $queue1->insert('First10',  10);

        $queue = new PriorityQueue();
        $queue->insert('First20',  20);

        $queue->merge($queue1);

        $queue->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First30','priority'=>30),$queue->extract());
        $this->assertEquals(array('data'=>'First20','priority'=>20),$queue->extract());
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue->extract());

        $queue1->setExtractFlags(PriorityQueue::EXTR_BOTH);
        $this->assertEquals(array('data'=>'First30','priority'=>30),$queue1->extract());
        $this->assertEquals(array('data'=>'First10','priority'=>10),$queue1->extract());
    }
}
