<?php
namespace asyncsql;

use asyncsql\connections\Connection;
use asyncsql\query\Query;

class QueryWorker extends \Thread{
    /** @var \ClassLoader */
    protected $classLoader = null;

    protected $dependents = [];

    /** @var \Threaded */
    protected $queue;

    /** @var Connection */
    protected $connection;

    protected $shutdown;

    /** @var \ThreadedLogger */
    protected $logger;

    public function __construct(\Threaded $queue, Connection $connection, \ThreadedLogger $logger){
        $this->queue = $queue;
        $this->shutdown = false;
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function run(){
        $this->registerClassLoader();
        gc_enable();
        ini_set("memory_limit", -1);

        $conn = $this->connection;
        $conn->open($this);

        $queue = $this->queue;

        while(!$this->shutdown){
            if(count($queue) > 0){
                /** @var Query $query */
                $query = $queue->shift();
                if(!$query->isGarbage()){
                    $query->doQuery($conn);
                }
            }
            $conn->onUpdate(time());
            if(count($queue) <= 0){
                usleep(200);
            }else{
                usleep(50);
            }
        }

        $conn->close();
        $this->free();
    }

    public function shutdown(){
        $this->shutdown = true;
    }

    public function getClassLoader(){
        return $this->classLoader;
    }

    public function setClassLoader(\ClassLoader $loader = null){
        $this->classLoader = $loader;
        $this->registerDependent($loader);
    }

    public function registerDependent($object){
        $ref = new \ReflectionClass($object);

        $file = $ref->getFileName();
        if($file and (array_search($file, $this->dependents) === false)){
            $this->dependents[] = $file;

            if(!$ref->isInterface()){
                foreach($ref->getInterfaces() as $interface){
                    $this->registerDependent($interface);
                }
            }

            if(($parent = $ref->getParentClass()) instanceof \ReflectionClass){
                $this->registerDependent($parent->getName());
            }
        }
    }

    public function getLogger(){
        return $this->logger;
    }

    public function registerClassLoader(){
        if(!interface_exists("ClassLoader", false)){
            foreach(array_reverse($this->dependents) as $dependent){
                /** @noinspection PhpIncludeInspection */
                require $dependent;
            }
        }
        if($this->classLoader !== null){
            $this->classLoader->register(true);
        }
    }

    public function getThreadName(){
        return "Asynchronous DataBase Query Thread";
    }

    public function free(){
        foreach($this as $k => $v){
            unset($this->{$k});
        }
    }

    public function __destruct(){
        $this->free();
    }
}