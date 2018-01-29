<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 0:27
 */
class Cache {
    protected $connection;
    private $countQueries = 0;
    private $sumQueriesTime = 0;

    private $default_time = 0;

    public function __construct($config) {
        $this->connection = new Memcache;
        $this->connection->connect($config['memcache_host'], $config['memcache_port'])
            or die("MemCache connection error");
        $this->default_time = $config['memcache_default_time'];
    }

    public function getValue($name)
    {
        // measures for profiling
        $this->countQueries++;
        $startTime = microtime(true);

        $value = @$this->connection->get($name);
        if(empty($value)){
            $value = false;
        }

        // measures for profiling
        $this->sumQueriesTime += (microtime(true) - $startTime) * 1000;
        return $value;
    }

    public function setValue($name, $value, $time=0)
    {
        if($time == 0) $time = $this->default_time;

        // measures for profiling
        $this->countQueries++;
        $startTime = microtime(true);

        $this->connection->set($name, $value, 0, $time);

        $this->sumQueriesTime += (microtime(true) - $startTime) * 1000;
    }

    public function incDecVal($name, $change)
    {
        if($change < 0) {
            return $this->connection->decrement($name, abs($change));
        }
        else {
            return $this->connection->increment($name, $change);
        }
    }

    public function getTechInfo() {
        return "cache: " . $this->countQueries . " req (" . round($this->sumQueriesTime, 2) . ")";
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}