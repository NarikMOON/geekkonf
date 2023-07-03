<?php
ob_start();
session_start();
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");
if (isset($_SESSION['login']) > 0) {
    unset($_SESSION['login']);
    unset($_SESSION['password']);
}
if ((isset($_GET['theme']) && $_GET['theme'] == 'dark')) {
    $_SESSION['theme'] = 'class="dark"';
} else if (isset($_GET['theme'])) $_SESSION['theme'] = ' ';


?><html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Регистрация</title>
</head>

<body <?php if (isset($_SESSION['theme']))echo $_SESSION['theme']; ?>>
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <a class='btn-toggle' href="?theme=<?php echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
    </div>

    <br>
    <form method=post style="vertical-align: middle; position: relative; margin: 0 40%">
        <table class="tab">
            <tr>
                <td>
                    <h1>Регистрация</h1>
                </td>
            </tr>
            <tr>
                <td><br>Укажите ваше имя</td>
            </tr>
            <tr>
                <td><input class="txt" type="text" name="name" size="25" maxlength="100" /></td>
            </tr>
            <tr>
                <td><br>Логин</td>
            </tr>
            <tr>
                <td><input class="txt" type="text" name="login" size="25" maxlength="20" /></td>
            </tr>
            <tr>
                <td><br>Пароль</td>
            </tr>
            <tr>
                <td><input class="txt" type="password" name="password1" size="25" maxlength="20" /></td>
            </tr>
            <tr>
                <td><br>Повторите пароль</td>
            </tr>
            <tr>
                <td><input class="txt" type="password" name="password2" size="25" maxlength="20" /></td>
            </tr>
            <tr>
                <td><input type="submit" style="width: 80%" class="but" formaction="authorization.php" value="Зарегестрироваться" /></td>
            </tr>
        </table>
    </form>
    <?php ob_end_flush(); ?>