<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 24.01.2018
 * Time: 21:02
 */
class DB {
    private $connection = false;

    private $countQueries = 0;
    private $sumQueriesTime = 0;

    public function __construct($config)
    {
        try
        {
            $this->connection = new PDO(
                "mysql:host={$config['host']};
                dbname={$config['db']}",
                $config['user'],
                $config['pass']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec('SET CHARACTER SET ' . $config['charset']);
            $this->connection->exec('SET NAMES ' . $config['charset']);
        }
        catch (PDOException $e)
        {
            echo "DB connection error: ".$e->getMessage()." ". $e->getCode();
            die();
        }
    }

    public function query($query, $args = array()) {
        if(!$this->connection) die("DB query before connected");

        // measures for profiling
        $this->countQueries++;
        $startTime = microtime(true);

        $dbh = $this->connection->prepare($query);
        $dbh->execute($args);

        $this->sumQueriesTime += (microtime(true) - $startTime) * 1000;
        
        return $dbh;
    }

    public function getOne($query, $args = array()) {
        return $this->query($query, $args)->fetchAll()[0][0];
    }

    public function getRow($query, $args = array()) {
        return $this->query($query, $args)->fetchAll()[0];
    }

    public function getAll($query, $args = array()) {
        return $this->query($query, $args)->fetchAll();
    } 

    public function insertId() {
        return $this->connection->lastInsertId();
    }

    public function getTechInfo() {
        return "db: " . $this->countQueries . " sreq (" . round($this->sumQueriesTime, 2) . ")";
    }
}
