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
    <title>Управление</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <?php
        if (isset($_SESSION['login']))$login = $_SESSION['login'];
        if (isset($_SESSION['password']))$password = $_SESSION['password'];
        if (!isset($login) || !isset($password)) {
            echo '<a  href="authorization.php" class="but">Вы ввели не все необходимые сведения!</a>';
            exit;
        }
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');
        $user = $mysqli->query("select * from users where login ='$login' and password = sha1('$password')");
        $u = $user->fetch_row();
        if ($u[0] < 1) {
            echo '<a  href="authorization.php" class="but">Логин и пароль не верны!</a>';
            exit;
        }
        if ($u[4] >= 900) {
            echo '<a style="width: 9%;background-color:rgb(64, 172, 205)" href="#" class="but">Управление</a>';
        }
        ?>
        <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
        <a style="width: 15%" href="recommendation.php" class="but">Рекомендации</a>
        <a class='btn-toggle' href="?theme=<?php echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>

        <a href="userpage.php">
            <table class="tab" style="float:right">
                <tr>
                    <?
                    $roleq = $mysqli->query("select role from accounts where id = '$u[4]'");
                    $role =  $roleq->fetch_row();?>
                    <td style="width: auto">"<? echo $role[0] ?>"</td>
                    <td style="width: auto"><? echo $u[1] ?>:</td>
                    <td style="width: auto"><? echo $u[0] ?></td>
                    </tr>
                    </table>
                    </a>

                    <?php
                    echo '<br><br>';
                    echo '<table class="tab" style="vertical-align: middle; position: relative; margin: 5% 20%"">';
                    if ($u[4] >= 900) {
                        echo '<tr><td><a style="width: 400px;" href="media_manag.php" class="but">Управление медиа</a></td></tr>';
                    }
                    if ($u[4] >= 900) {
                        echo '<tr><td><a style="width: 400px;" href="genres_manag.php" class="but">Управление жанрами</a></td></tr>';
                    }
                    if ($u[4] >= 900) {
                        echo '<tr><td><a style="width: 400px;" href="lr10.php" class="but">ЛР10</a></td></tr>';
                    }
                    if ($u[4] >= 1300) {
                        echo '<tr><td><a style="width: 400px;" href="users_manag.php" class="but">Управление пользователями</a></td></tr>';
                    }
                    if ($u[4] >= 1300) {
                        echo '<tr><td><a style="width: 400px;" href="mailjob.php" class="but">Рассылки</a></td></tr>';
                    }
                    echo '</table>';
                    ?>
</body>

</html>
<?php ob_end_flush(); ?>