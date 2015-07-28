<?php
namespace asyncsql\connections;

use asyncsql\lang\ConnectionCloseException;
use asyncsql\lang\ConnectionOpenException;
use asyncsql\QueryWorker;

/**
 * Interface Connection
 *
 * @package asyncsql\connections
 */
interface Connection{
    /**
     * @param QueryWorker $worker
     * @throws ConnectionOpenException
     */
    public function open(QueryWorker $worker);

    /**
     * @throws ConnectionCloseException
     */
    public function close();

    /**
     * @param $time
     */
    public function onUpdate($time);

    /**
     * @return bool
     */
    public function isValid();
}