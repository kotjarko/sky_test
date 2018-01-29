<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 6:03
 */
$message = "";
if(isset($_POST['login']))
{
    $password = $_POST['password'] ?? '';
    $tryLogin = $USER->login($_POST['login'], $password);
    if($tryLogin == 0) {
        $message = "<div class=\"alert alert-danger small\">User already exists or wrong password.</div>";
    }
    else {
        header('location: ./');
    }
}