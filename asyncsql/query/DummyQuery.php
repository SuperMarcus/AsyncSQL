<?php
namespace asyncsql\query;

use asyncsql\connections\Connection;

class DummyQuery extends Query{

    public function doQuery(Connection $conn){

    }
}