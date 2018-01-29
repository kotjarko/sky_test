<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 6:20
 */
?>
    <h2 class="text-center">
        GAME - <?=$USER->player->getDataField('login');?>
    </h2>
    <h4 class="text-center">
        Рейтинг: <?=$USER->player->getDataField('rating');?>
    </h4>
    <a href="?page=duel_start">
        <button class="form-control btn btn-large btn-primary" type="button" style="margin-bottom: 10px" <?=$duelBtnDisable;?>>
            <?=$btnText;?>
        </button>
    </a>
    <a href="?page=logout">
        <button class="form-control btn btn-large btn-warning" type="button">
            Выход
        </button>
    </a>

