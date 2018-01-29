<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 6:20
 */

$duelBtnDisable = "";
$btnText = "Начать дуэль";
if($USER->player->getDataField("ready") == 1) {
    $duelBtnDisable = " disabled ";
    if($USER->player->getDataField("in_duel") == 0) $btnText = "Ожидание противника";
    else {
        header("location: ?page=duel");
    }
}
