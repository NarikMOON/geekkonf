<?php
//error_reporting(1);
//ini_set('display_errors', 1);
ob_start();
session_start();
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");

if ((isset($_GET['theme']) && $_GET['theme'] == 'dark')) {
    $_SESSION['theme'] = 'class="dark"';
} else if (isset($_GET['theme'])) $_SESSION['theme'] = ' ';

?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Управление пользователями</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <?php
        $login = $_SESSION['login'];
        $password = $_SESSION['password'];
        if (!$login || !$password) {
            echo 'Вы ввели не все необходимые сведения!</br>';
            var_dump($login);
            var_dump($password);
            exit;
        }
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');
        $user = $mysqli->query("select * from users where login ='$login' and password = sha1('$password')");
        $u = $user->fetch_row();
        if ($u[0] < 1) {
            echo '<h3>Логин и пароль не верны!</h3>';
            exit;
        }
        if ($u[4] >= 900) {
            echo '<a style="width: 9%;background-color:rgb(64, 172, 205)" href="management_panel.php" class="but">Управление</a>';
        } ?>
        <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
        <a style="width: 15%" href="recommendation.php" class="but">Рекомендации</a>
        <a class="btn-toggle" href="?theme=<? echo ((isset($_SESSION['theme'])) && ($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
        <a href="userpage.php">
            <table class="tab" style="float:right">
                <tr>
                    <?
                    $roleq = $mysqli->query("select role from accounts where id = '$u[4]'");
                    $role =  $roleq->fetch_row(); ?>
                    <td style="width: auto">"<?php echo $role[0] ?>"</td>
                    <td style="width: auto"><?php echo $u[1] ?>:</td>
                    <td style="width: auto"><?php echo $u[0] ?></td>
                </tr>
            </table>
        </a>        
        <br><br>
        <?php
        if (isset($_GET['do'])) {
            if ($_GET['do'] == 1) {
                $uid = $_GET['uid'];
                $newname = $_POST['utitle'];
                $mysqli->query("update users set name ='$newname' where id = '$uid'");
            }
            if ($_GET['do'] == 2) {
                $uid = $_GET['uid'];
                $newacc = $_POST['uaccount'][0];
                $mysqli->query("update users set account ='$newacc' where id = '$uid'");
            }
            if ($_GET['do'] == 3) {
                $uid = $_GET['uid'];
                $mysqli->query("delete from users where id = '$uid'");
                $mysqli->query("delete from marks where uid = '$uid'");
                $mysqli->query("delete from comments where uid = '$uid'");
                $mysqli->query("delete from media where id in (select mid from recommends where uid='$uid')");
                $mysqli->query("delete from recommends where uid = '$uid'");
            }
            if ($_GET['do'] == 4) {
                $newlogin = $_POST['newlogin'];
                $newname = $_POST['newname'];
                $newpassword = $_POST['newpassword'];
                $acc = $_POST['newaccount'][0];
                $result = $mysqli->query("select * from users where login = '$newlogin'");
                $res =  $result->fetch_row();
                if ($res) {
                    echo 'Указанный логин занят, пожалуйста повторите регистрацию';
                    exit;
                }
                $mysqli->query("insert into users values (NULL, '$newname', '$newlogin', sha1('$newpassword'), '$acc', now())");
                if ($mysqli->error) {
                    echo 'При регистрации произошла ошибка';
                } else echo 'Регистрация успешна';
            }
        }

        $usersq = $mysqli->query("select * from users"); ?>
        <table class="tab" style="vertical-align:top">
            <tr>
                <td></td>
                <td>Управление пользователями</td>
            </tr>
            <?
            while ($users = $usersq->fetch_row()) {
                echo '<form  method=post ><tr>';
                echo '<td>#' . $users[0] . ' ' . $users[2] . '</td>';
                echo '<td><input class="txt" type="text" name="utitle" size="30" maxlength="30" value="' . $users[1] . '" />';
                echo '<input type="submit" style="width: auto" class="but" formaction="users_manag.php?uid=' . $users[0] . '&do=1" value="Переименовать"></input></td>'; //

                $accountsq = $mysqli->query("select * from accounts");
                echo '<td><fieldset class="filter" name="accounts">';
                echo ' <legend>Тип аккаунта:</legend>';
                while ($acc =  $accountsq->fetch_row()) {
                    echo '<div ><input type="radio" name="uaccount[]" value="' . $acc[0] . ($acc[0] == $users[4] ? '" checked>' : '">');
                    echo '<label for=' . $acc[0] . '>' . $acc[1] . '</label>';
                    echo '</div>';
                }
                echo '</fieldset></div></div><input type="submit" style="width: auto" class="but" formaction="users_manag.php?uid=' . $users[0] . '&do=2" value="Изменить"></input></td>';
                echo '<td><input type="submit" style="width: auto; background-color:red;" class="but" formaction="users_manag.php?uid=' . $users[0] . '&do=3" value="Удалить"></input></td>'; //
                echo '</tr></form>';
            } ?>
            <tr>
                <td></td>
                <td>Добавить пользователя</td>
            </tr>
            <form method=post>
                <tr>
                    <td>#new Логин <input class="txt" type="text" name="newlogin" size="20" maxlength="20" /></td>
                    <td>Имя <input class="txt" type="text" name="newname" size="80" maxlength="100" /></td>
                    <td>Пароль<input class="txt" type="text" name="newpassword" size="30" maxlength="30" /></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <fieldset class="filter" name="newaccounts">
                            <legend>Тип аккаунта:</legend>
                            <?
                            $accountsq = $mysqli->query("select * from accounts");
                            while ($acc =  $accountsq->fetch_row()) {
                                echo '<div ><input type="radio" name="newaccount[]" value="' . $acc[0] . '">';
                                echo '<label for=' . $acc[0] . '>' . $acc[1] . '</label>';
                                echo '</div>';
                            } ?>
                        </fieldset>
    </div>
    </div>
    <td><input type="submit" style="width: auto; background-color:green;" class="but" formaction="users_manag.php?do=4" value="Добавить"></input></td>
    </tr>
    </form>
    </table>

</body>

</html>
<?php ob_end_flush(); ?>