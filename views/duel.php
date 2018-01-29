<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 8:12
 */
?>
<h2 class="text-center">
    <?=$topText?>
</h2>
<div class="panel panel-success">
    <div class="panel-heading">Вы: - <?=$USER->player->getDataField('login');?></div>
    <div class="panel-body">
        <div class="panel-body">
            Атака: <?=$USER->player->getDataField('damage');?>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width:<?=$own_health;?>%">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-warning">
    <div class="panel-heading">Против: - <?=$enemy->getDataField('login');?></div>
    <div class="panel-body">
        Атака: <?=$enemy->getDataField('damage');?>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width:<?=$enemy_health;?>%">
            </div>
        </div>
    </div>
</div>
<form method="post">
    <input type="hidden" name="attack" value="0"/>
    <button class="form-control btn btn-large btn-primary" type="submit" style="margin-bottom: 10px" <?=$duelBtnDisable;?>>
        Атаковать
    </button>
</form>
<ul class="list-group">
    <?=$actions_list;?>
</ul>