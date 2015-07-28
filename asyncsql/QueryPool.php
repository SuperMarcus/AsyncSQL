<?php
namespace asyncsql;

use asyncsql\connections\Connection;
use asyncsql\query\Query;

class QueryPool implements \Countable{
    protected $size = 0;

    /** @var \Threaded */
    private $queue;

    /** @var QueryWorker[] */
    private $workers = [];

    /** @var Connection */
    private $connection;

    /** @var \ClassLoader */
    private $classLoader;

    /** @var \ThreadedLogger */
    private $logger;

    /** @var Query[] */
    private $querys;

    public function __construct($size, Connection $connection, \ClassLoader $loader, \ThreadedLogger $logger){
        $this->queue = new \Threaded();
        $this->connection = $connection;
        $this->classLoader = $loader;
        $this->logger = $logger;
        $this->increaseSize($size);
    }

    public function addQuery(Query $query){
        if(!$query->isGarbage()){
            $this->queue[] = $query;
            $this->querys[] = $query;
        }
    }

    /**
     * @param callable $callback
     */
    public function doCollection(callable $callback){
        foreach($this->querys as $query){
            if($query->isGarbage()){
                $callback($query);
            }
        }
    }

    public function shutdown(){
        foreach($this->workers as $worker){
            $worker->shutdown();
            $worker->join();
        }
        $this->workers = [];
        $this->size = 0;
    }

    /**
     * @param int $newSize
     */
    public function increaseSize($newSize){
        $newSize = (int) $newSize;
        if($newSize > $this->size){
            for($i = $this->size; $i < $newSize; ++$i){
                $this->workers[$i] = new QueryWorker($this->queue, $this->connection, $this->logger);
                $this->workers[$i]->setClassLoader($this->classLoader);
                $this->workers[$i]->start();
            }
            $this->size = $newSize;
        }
    }

    public function getStackCount(){
        return count($this->queue);
    }

    /**
     * @return int
     */
    public function count(){
        return $this->size;
    }

    public function __destruct(){
        $this->shutdown();
    }
}