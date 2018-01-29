<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 8:12
 */
if($USER->player->getDataField("in_duel") == 0) header("location: ./");
$duelBtnDisable = "";
$DUEL = new Duel($MYSQL, $MEMCACHE, $USER->player->getDataField('in_duel'));

$enemy = $DUEL->getEnemyPlayer($USER->getId());

$own_health = 100;
$enemy_health = 100;

if($DUEL->getRemainTime() < 30) {
    $duelBtnDisable = " disabled ";
    $topText = "До начала осталось: " . (30 - $DUEL->getRemainTime());
}
else {
    $topText = "Дуэль!";
    if(isset($_POST['attack'])) {
        $DUEL->attack($USER->getId());
    }
    $healths = $DUEL->getHealthData();
    $own_health = ($healths[$USER->getId()] / $USER->player->getDataField('health')) * 100;
    $enemy_health = ($healths[$enemy->getId()] / $enemy->getDataField('health')) * 100;
    
    $actions_list = "";
    $actions = $DUEL->getActions();
    $your_dir = $DUEL->getActionsDir($USER->getId());
    foreach ($actions AS $item) {
        $actions_list .= "<li class='list-group-item'>";
        if($item['action'] == 0) {
            if($item['direction'] == $your_dir) {
                $actions_list .= "Вы ударили " . $enemy->getDataField('login') . " на {$item['value']} урона";
            }
            else {
                $actions_list .= $enemy->getDataField('login') . " ударил Вас на {$item['value']} урона";
            }
        }
        else {
            if($item['direction'] == $your_dir) {
                $actions_list .= "Вы убили " . $enemy->getDataField('login');
            }
            else {
                $actions_list .= $enemy->getDataField('login') . " убил вас.";
            }
        }
        $actions_list .= "</li>";
    }
}
