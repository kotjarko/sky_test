<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 7:14
 */
if($USER->player->getDataField("ready") == 0) {
    $USER->player->updateDataField("ready", "1");
    $USER->player->updateDataField("ready_set", date("Y-m-d H:i:s"));
    // TODO check if already have ready
    $possibleEnemy = $MYSQL->getOne("SELECT user_id FROM players WHERE ready = 1 AND in_duel = 0 AND user_id <> :id ORDER BY ready_set", array(":id" => $USER->getId()));
    if($possibleEnemy != "") {
        // TODO create duel
        $duel = new Duel($MYSQL, $MEMCACHE);
        $duel->startNewDuel($USER->getId(), $possibleEnemy);
    }
}
 header('location: ./');