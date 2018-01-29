<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 24.01.2018
 * Time: 21:01
 */
class User
{
    private $db = false;
    private $memcache = false;

    private $userId = 0;
    public $player = null;
    public $isLoggedIn = false;

    public function __construct($db, $memcache)
    {
        $this->db = $db;
        $this->memcache = $memcache;
        session_start();

        if(isset($_SESSION['user_id']) && isset($_SESSION['user_hash'])) {
            // hash for this user id and ip address
            if($this->generateSessionHash($_SESSION['user_id']) == $_SESSION['user_hash'])
            {
                // looking for session hash in cache or db
                    $sessionHash = $this->memcache->getValue("session_" . $_SESSION['user_id']);
                if($sessionHash == false) {
                    $sessionHash = $this->db->getOne("SELECT hash FROM users WHERE id = :id", array(':id' => $_SESSION['user_id']));
                }
                if($sessionHash == $_SESSION['user_hash']) {
                    $this->isLoggedIn = true;
                    $this->userId = $_SESSION['user_id'];
                    $this->memcache->setValue("session_" . $this->userId, $sessionHash, 60 * 10);
                    $this->player = new Player($this->db, $this->memcache, $this->userId);
                }
            }
            if(!$this->isLoggedIn) $this->logout();
        }
    }

    public function getId()
    {
        return $this->userId;
    }

    private function register($login, $password)
    {
        $data = array(
            ':login' => $login,
            ':password' => md5($password),
        );
        $this->db->query("INSERT INTO users (login, password) VALUES (:login, :password)", $data);
        return $this->db->insertId();
    }

    public function login($login, $password)
    {
        $data = array(':login' => $login);
        $userData = $this->db->getRow("SELECT * FROM users WHERE login = :login", $data);

        if($userData == "") {
            $this->userId = $this->register($login, $password);
        }
        else {
            if($userData['password'] == md5($password)) {
                $this->userId = $userData['id'];
            }
        }

        if($this->userId != 0) {
            session_start();
            $hash = $this->generateSessionHash($this->userId);
            $data = array(
                ":hash" => $hash,
                ":ip" => $this->getUserIP(),
                ":id" => $this->userId
            );
            $this->db->query("UPDATE users SET hash = :hash, ip = :ip WHERE id = :id", $data);
            $this->memcache->setValue("session_" . $this->userId, $hash, 60 * 10);
            $_SESSION['user_id'] = $this->userId;
            $_SESSION['user_hash'] = $hash;
            $this->isLoggedIn = true;
            $this->player = new Player($this->db, $this->memcache, $this->userId);
        }
        return $this->userId;
    }

    private function generateSessionHash($userId)
    {
        return md5($userId . $this->getUserIP());
    }

    private function getUserIP()
    {
        // REMOTE_ADDR may not actually contain
        // real client IP address if client use proxy
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        return filter_var($ipAddress, FILTER_VALIDATE_IP);
    }

    public function logout()
    {
        session_destroy();
    }
}