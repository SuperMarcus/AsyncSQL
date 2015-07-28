<?php
namespace asyncsql\connections;

use asyncsql\lang\ConnectionOpenException;
use asyncsql\QueryWorker;

class MySQLConnection extends \mysqli implements Connection{
    private $host;

    private $port;

    private $user;

    private $password;

    private $database;

    private $socket;

    private $lastUpdate;

    protected $worker;

    public function __construct($host = null, $user = null, $password = null, $database = "", $port = -1, $socket = null){
        $this->setHost($host === null ? @ini_get("mysqli.default_host") : $host);
        $this->setUser($user === null ? @ini_get("mysqli.default_user") : $user);
        $this->setPassword($password === null ? @ini_get("mysqli.default_pw") : $password);
        $this->setDatabase($database);
        $this->setPort($port < 0 ? @ini_get("mysqli.default_port") : $port);
        $this->setSocket($socket === null ? @ini_get("mysqli.default_socket") : $socket);
    }

    public function onUpdate($time){
        if(($time - $this->lastUpdate) > 10 * 60){
            $this->ping();
            $this->lastUpdate = $time;
        }
    }

    /**
     * @param QueryWorker $worker
     * @throws ConnectionOpenException
     */
    public function open(QueryWorker $worker){
        $this->worker = $worker;

        $this->connect($this->getHost(), $this->getUser(), $this->getPassword(), $this->getDatabase(), $this->getPort(), $this->getSocket());

        if(mysqli_connect_error()) {
            throw new ConnectionOpenException('DataBase Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error());
        }

        $this->lastUpdate = time();
    }

    public function close(){
        parent::close();
        $this->worker = null;
    }

    /**
     * @return string
     */
    public function getHost(){
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host){
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(){
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port){
        $this->port = (int) $port;
    }

    /**
     * @return string
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user){
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword(){
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password){
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDatabase(){
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database){
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getSocket(){
        return $this->socket;
    }

    /**
     * @param string $socket
     */
    public function setSocket($socket){
        $this->socket = $socket;
    }

    /**
     * @return bool
     */
    public function isValid(){
        return $this->get_connection_stats();
    }
}