<?php
/**
 * Created by PhpStorm.
 * User: Владимир
 * Date: 25.01.2018
 * Time: 6:03
 */
?>

    <form class="form-signin" method="post">
        <h2 class="form-signin-heading text-center">
            Необходима авторизация
        </h2>
        <?=$message;?>
        <input name="login" type="text" class="input-block-level form-control" placeholder="Имя пользователя" id="login"\>
        <input name="password" type="password" class="input-block-level form-control" placeholder="Пароль" id="password"\>
        <button class="form-control btn btn-large btn-primary" id="submit" type="submit">
            Войти в систему
        </button>
    </form>
