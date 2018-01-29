<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 24.01.2018
 * Time: 20:55
 */
$startTime = microtime(true);

// TODO REMOVE
error_reporting(-1);

// classes autoloader
spl_autoload_register(function ($class_name) {
    $file = dirname(__FILE__) . '/include/' . $class_name . '.php';
    if(!file_exists($file)) {
        die('Autoload class failed: ' . $class_name . ' not found');
    }
    include $file;
});

// connect MySQL and Memcache
$CONFIG = include("include/config.php");
$MYSQL = new DB($CONFIG);
$MEMCACHE = new Cache($CONFIG);
$USER = new User($MYSQL, $MEMCACHE);

// simple routing
// may use real router and controllers
// for each page, but not in this simple project
$page = "main";
if(!$USER->isLoggedIn) {
    $page = "login";
}
else {
    if(isset($_GET['page'])) {
        $page = $_GET['page'];
    }
}

// page logic
include 'models/' . $page . '.php';

// page view
include 'views/head.php';
include 'views/' . $page . '.php';

// profiling and page tail
$totalTime = round((microtime(true) - $startTime) * 1000, 2);
$profilingResult = "page: " . $totalTime . " ms, " . $MYSQL->getTechInfo() . ", " . $MEMCACHE->getTechInfo();
include 'views/tail.php';