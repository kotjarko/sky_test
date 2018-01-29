<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 6:50
 */
class Player {
    private $db = false;
    private $memcache = false;
    private $id = 0;
    private $data = null;

    public function __construct($db, $memcache, $id)
    {
        $this->db = $db;
        $this->memcache = $memcache;
        $this->id = $id;
        $this->loadPlayerData();
    }

    private function loadPlayerData()
    {
        $data = $this->memcache->getValue("player_data_" . $this->id);
        if($data == false) {
            $data = $this->db->getRow("SELECT u.login, p.* FROM users AS u LEFT JOIN players AS p ON u.id = p.user_id WHERE u.id = :id", array(':id' => $this->id));
            $this->memcache->setValue("player_data_" . $this->id, $data, 60 * 10);
        }
        $this->data = $data;
    }

    public function getDataField($fieldName) {
        return $this->data[$fieldName];
    }

    public function updateDataField($fieldName, $fieldValue) {
        $this->data[$fieldName] = $fieldValue;
        $this->memcache->setValue("player_data_" . $this->id, $this->data, 60 * 10);
        $data = array(
            ":val" => $fieldValue,
            ":id" => $this->id
        );
        $this->db->query("UPDATE players SET {$fieldName} = :val WHERE user_id = :id", $data);
    }

    public function getId() {
        return $this->id;
    }
}