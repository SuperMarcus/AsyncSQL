<?php
namespace asyncsql\query;

use asyncsql\connections\Connection;

abstract class Query extends \Collectable{
    private $result = null;

    private $serialized = false;

    abstract public function doQuery(Connection $conn);

    /**
     * @param mixed $result
     * @param bool  $serialize
     */
    public function setResult($result, $serialize = true){
        $this->result = $serialize ? serialize($result) : $result;
        $this->serialized = $serialize;
    }

    /**
     * @return bool
     */
    public function hasResult(){
        return $this->result !== null;
    }

    /**
     * @return mixed
     */
    public function getResult(){
        return $this->serialized ? unserialize($this->result) : $this->result;
    }
}