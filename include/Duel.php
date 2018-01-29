<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 7:48
 */
class Duel
{
    private $db = false;
    private $memcache = false;

    private $id = 0;

    private $player1 = null;
    private $player2 = null;

    private $created = 0;

    private $health1 = 100;
    private $health2 = 100;

    private $actions = false;
    
    public function __construct($db, $memcache, $id = 0)
    {
        $this->db = $db;
        $this->memcache = $memcache;
        if($id != 0) {
            $this->id = $id;
            $this->loadDuelData();
        }
    }

    public function startNewDuel($id1, $id2)
    {
        $this->player1 = new Player($this->db, $this->memcache, $id1);
        $this->player2 = new Player($this->db, $this->memcache, $id2);
        $data = array(
            ":id1" => $id1,
            ":id2" => $id2,
            ":health1" => $this->player1->getDataField('health'),
            ":health2" => $this->player1->getDataField('health'),
            ":date" => date("Y-m-d H:i:s")
        );
        $this->db->query("INSERT INTO duels (id1, id2, current_health_1, current_health_2, created) VALUES (:id1, :id2, :health1, :health2, :date)", $data);
        $this->id = $this->db->insertId();
        $this->loadDuelData();
        $this->player1->updateDataField('in_duel', $this->id);
        $this->player2->updateDataField('in_duel', $this->id);
    }

    public function loadDuelData() {
        $data = $this->memcache->getValue("duel_" . $this->id);
        if($data == false) {
             $data = $this->db->getRow("SELECT * FROM duels WHERE id = :id", array(':id' => $this->id));
             $this->memcache->setValue("duel_" . $this->id, $data, 60 * 10);
        }

        if($this->player1 == null) $this->player1 = new Player($this->db, $this->memcache, $data['id1']);
        if($this->player2 == null) $this->player2 = new Player($this->db, $this->memcache, $data['id2']);
        $this->created = $data['created'];
        $this->health1 = $data['current_health_1'];
        $this->health2 = $data['current_health_2'];

        $this->actions = $this->memcache->getValue("duel_actions_" . $this->id);
        if($this->actions == false) {
            $this->actions = $this->db->getAll("SELECT * FROM actions WHERE duel_id = :id ORDER BY id", array(':id' => $this->id));
            $this->memcache->setValue("duel_actions_" . $this->id, $this->actions, 60 * 10);
        }
    }

    private function updateData() {
        $data = $this->memcache->getValue("duel_" . $this->id);
        if($data == false) {
            $data = $this->db->getRow("SELECT * FROM duels WHERE id = :id", array(':id' => $this->id));
        }
        $data['current_health_1'] = $this->health1;
        $data['current_health_2'] = $this->health2;
        $vals = array(
            ":health1" => $this->health1,
            ":health2" => $this->health2,
            ":id" => $this->id
        );
        $this->db->query("UPDATE duels SET current_health_1 = :health1, current_health_2 = :health2 WHERE id = :id", $vals);
        $this->memcache->setValue("duel_" . $this->id, $data, 60 * 10);
    }

    public function attack($attackerId) {
        $direction = 0;
        $damage = 0;
        $action = 0;

        if($this->player1->getId() == $attackerId) {
            $this->health2 -= $this->player1->getDataField('damage');
            $damage = $this->player1->getDataField('damage');
        }
        else {
            $this->health1 -= $this->player2->getDataField('damage');
            $direction = 1;
            $damage = $this->player2->getDataField('damage');
        }
        $this->updateData();
        
        if(($this->health1 <= 0) || ($this->health2 <= 0)) {
            $action = 1;
            $this->finishDuel();
        }

        // direction: 0 = 1->2, 1 = 2->1
        // action: 0 = attack, 1 = kill
        $vals = array(
            ":id" => $this->id,
            ":dir" => $direction,
            ":action" => $action,
            ":val" => $damage,
        );
        $this->db->query("INSERT INTO actions (duel_id, direction, action, value) VALUES (:id, :dir, :action, :val)", $vals);
        $this->actions = $this->db->getAll("SELECT * FROM actions WHERE duel_id = :id ORDER BY id", array(':id' => $this->id));
        $this->memcache->setValue("duel_actions_" . $this->id, $this->actions, 60 * 10);
    }

    public function getRemainTime() {
        return time() - strtotime($this->created);
    }
    
    public function getEnemyPlayer($selfId) {
        if($this->player1->getId() == $selfId) return $this->player2;
        return $this->player1;
    }

    public function getHealthData() {
        return array(
            $this->player1->getId() => $this->health1,
            $this->player2->getId() => $this->health2,
        );
    }

    public function getActions() {
        return $this->actions;
    }

    public function getActionsDir($selfId) {
        if($this->player1->getId() == $selfId) return 0;
        return 1;
    }

    private function finishDuel() {
//        По окончании дуэли победитель получает +1 рейтинга, проигравший получает -1 рейтинга.
//        Независимо от победы или проигрыша каждый игрок получает +1 к параметру урон и +1 к параметру жизни.
        if($this->health1 <= 0) {
            $this->player1->updateDataField("rating", $this->player1->getDataField("rating") - 1);
            $this->player2->updateDataField("rating", $this->player2->getDataField("rating") + 1);
        }
        else {
            $this->player1->updateDataField("rating", $this->player1->getDataField("rating") + 1);
            $this->player2->updateDataField("rating", $this->player2->getDataField("rating") - 1);
        }
        $this->player1->updateDataField("health", $this->player1->getDataField("health") + 1);
        $this->player2->updateDataField("health", $this->player2->getDataField("health") + 1);
        $this->player1->updateDataField("damage", $this->player1->getDataField("damage") + 1);
        $this->player2->updateDataField("damage", $this->player2->getDataField("damage") + 1);

        $this->player1->updateDataField('ready', "0");
        $this->player2->updateDataField('ready', "0");

        $this->player1->updateDataField('in_duel', "0");
        $this->player2->updateDataField('in_duel', "0");
    }
}