<?php
namespace asyncsql\query;

use asyncsql\connections\Connection;
use asyncsql\connections\MySQLConnection;
use asyncsql\lang\InvalidConnectionException;

/**
 * Sample of using Query
 *
 * @package asyncsql\query
 */
class MySQLQuery extends Query{
    const FETCH_ARRAY = 0;
    const FETCH_ALL = 1;
    const FETCH_FIRST = 2;

    private $statement;

    private $fetch;

    public function doQuery(Connection $conn){
        if($conn instanceof MySQLConnection){
            $result = $conn->query($this->statement);
            if($result instanceof \mysqli_result){
                switch($this->fetch){
                    case MySQLQuery::FETCH_FIRST:
                        $this->setResult($result->fetch_assoc());
                        break;
                    case MySQLQuery::FETCH_ALL:
                        $this->setResult($result->fetch_all());
                        break;
                    case MySQLQuery::FETCH_ARRAY:
                        $this->setResult($result->fetch_array());
                        break;
                }
                $result->close();
            }
        }else{
            $this->setGarbage();
            throw new InvalidConnectionException("Connection pass to ".MySQLQuery::class." must be an instance of ".MySQLConnection::class.", ".get_class($conn)." given.");
        }

        $this->setGarbage();//Don't forget to set to garbage after execute finishes
    }

    public function __construct($statement, $fetch = MySQLQuery::FETCH_ARRAY){
        $this->statement = $statement;
        $this->fetch = $fetch;
    }
}