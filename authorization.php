<?php
session_start();
ob_start();
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");

if ((isset($_GET['theme']) && $_GET['theme'] == 'dark')) {
    $_SESSION['theme'] = 'class="dark"';
}else if (isset($_GET['theme']))$_SESSION['theme'] = ' '; ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Авторизация</title>
</head>
<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?> >
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%; background-color:rgb(64, 172, 205)" href="authorization.php" class="but">Авторизация</a>
        <a style="width: 9%" href="registration.php" class="but">Регистрация</a>
        <a style="width: 9%" href="messenger.php" class="but">Чат</a>
        <a class='btn-toggle' href="?theme=<?php echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
    </div>    
    <?php
    if (isset($_SESSION['login']) > 0) {
        unset($_SESSION['login']);
        unset($_SESSION['password']);
    }
    if (count($_POST) > 0) {
        $name = $_POST['name'];
        $login = $_POST['login'];
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306'); ?>
        <form style="vertical-align: middle; position: relative; margin: 0 40%">
            <table class="tab">
                <tr>
                    <td>
                        <h1>
                            <?php
                            if (!$name || !$login || !$password1 || !$password2) {
                                echo 'Вы ввели не все поля';
                                unset($_POST);
                                exit;
                            }
                            if ($password1 != $password2) {
                                echo 'Вы не верно повторили пароль, пожалуйста повторите регистрацию';
                                unset($_POST);
                                exit;
                            }
                            $result = $mysqli->query("select * from users where login = '$login'");
                            $res = $result->fetch_row();
                            if ($res) {
                                echo 'Указанный логин занят, пожалуйста повторите регистрацию';
                                unset($_POST);
                                exit;
                            }
                            $mysqli->query("insert into users values (NULL, '$name', '$login', sha1('$password1'), 1, now() )");
                            if ($mysqli->error) {
                                echo 'При регистрации произошла ошибка';
                                unset($_POST);
                            } else echo 'Регистрация успешна';
                            echo $mysqli->error; ?>
                        </h1>
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }
    ?>
    <form method=post style="vertical-align: middle; position: relative; margin: 0 40%">
        <table class="tab">
            <tr>
                <td>
                    <h1>Авторизация</h1>
                </td>
            </tr>
            <tr>
                <td>Логин</td>
            </tr>
            <tr>
                <td><input class="txt" type="text" name="login" size="25" maxlength="20" value=<?php if (isset($_POST['login']))echo '"' . $_POST['login'] . '"' ?> ></td>
            </tr>
            <tr>
                <td>Пароль</td>
            </tr>
            <tr>
                <td><input class="txt" type="password" name="password" size="25" maxlength="20" value=<?php if (isset($_POST['password'])) echo '"' . $_POST['password'] . '"' ?> ></td>
            </tr>
            <tr>
                <td><input type="submit" style="width: 80%" class="but" formaction="recommendation.php" value="Вход" ></td>
            </tr>
        </table>
    </form>
</body>

</html>

<?php ob_end_flush(); ?>