<?php
//error_reporting(1);
//ini_set('display_errors', 1);
ob_start();
session_start();
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");

if ((isset($_GET['theme']) && $_GET['theme'] == 'dark')) {
    $_SESSION['theme'] = 'class="dark"';
}else if (isset($_GET['theme'])) $_SESSION['theme'] = ' ';

?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Чат</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    <a href="#openModal" style="text-decoration:none">
        <div class="header">Geek Konf</div>
    </a>
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <?php
        if (isset($_SESSION['login'])) $login = $_SESSION['login'];
        if (isset($_SESSION['password'])) $password = $_SESSION['password'];
        $aucheck = true;
        if (!isset($login) || !isset($password)) {
            $aucheck = false;
            echo "<a  href='authorization.php' class='but'>Вы не авторизованы!</a>";
        }
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');

        if ($aucheck) {
            $user = $mysqli->query("select * from users where login ='$login' and password = sha1('$password')");
            $u = $user->fetch_row();
            if ($u[0] < 1) {
                echo '<a  href="authorization.php" class="but">Логин и пароль не верны!Вы не авторизованы!</a>';
                $aucheck = false;
            }
        }
        if ($aucheck) {
            if ($u[4] >= 900) {
                echo '<a style="width: 9%" href="management_panel.php" class="but">Управление</a>';
            } ?>
            <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
            <a style="width: 15%;" href="recommendation.php" class="but">Рекомендации</a>
            <?
        } ?>
            <a class="btn-toggle" href="?theme=<? echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
            <? if ($aucheck) { ?>  
            <a href="userpage.php">
                <table class="tab" style="float:right">
                    <tr>
                        <?
                        $roleq = $mysqli->query("select role from accounts where id = '$u[4]'");
                        $role =  $roleq->fetch_row(); ?>
                        <td style="width: auto">"<? echo $role[0] ?>"</td>
                        <td style="width: auto"><? echo $u[1] ?>:</td>
                        <td style="width: auto"><? echo $u[0] ?></td>
                    </tr>
                </table>
            </a>
        <?
        } ?>
        <?php
        if (isset($_GET['sendmessage'])) {
            if ($_GET['sendmessage'] == 100) {
                $text = $_POST['message_text'];
                if ($text != '') {
                    $mysqli->query("insert into messages value (NULL, $u[0], 0, '$text', NOW() )");
                }
            }
            if ($_GET['sendmessage'] == 120) {
                $text = $_POST['message_text'];
                $addressedto = $_GET['addressedto'];
                if ($text != '') {
                    $mysqli->query("insert into messages value (NULL, $u[0], $addressedto, '$text', NOW() )");
                }
            }
        }
        if (isset($_GET['sendlike'])) {
            $pid = $_GET['addressedto'];
            $mysqli->query("delete from likesofchat where uid=$u[0] and pid=$pid");

            if ($_GET['sendlike'] == 1) {
                $mysqli->query("insert into likesofchat value (NULL, $u[0], $pid, 1,NOW())");
            }
            if ($_GET['sendlike'] == 2) {
                $mysqli->query("insert into likesofchat value (NULL, $u[0], $pid, 2,NOW())");
            }
        }
        $messegesq = $mysqli->query("select * from messages");
        while ($messeg =  $messegesq->fetch_row()) {
            $messeges[] = $messeg;
        }
        for ($i = 0, $c = count($messeges); $i < $c; $i++) {
            $new_arr[$messeges[$i][2]][] = $messeges[$i];
        }

        function my_sort($data, $parent = 0, $level = 0)
        {
            global $login, $password, $aucheck;
            $arr = $data[$parent];
            $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');

            for ($i = 0; $i < count($arr); $i++) {
                echo '<div style="padding-left:' . $level . 'px;">';
                $userid = $arr[$i][1];
                $writerq = $mysqli->query("select * from users where id=$userid");
                $writer =  $writerq->fetch_row();
                echo "<table class='tab'><tr><td>Пишет #" . $writer[1] . "</td><td> Когда: " . $arr[$i][4] . '</td></tr></table><br>';
                echo "<table class='tab' style='background-color:gray'><tr><td>" . $arr[$i][3] . "</td></tr></table><br>";
                if ($aucheck) {
                    echo '<a  class="but" href="#openModal' . $arr[$i][0] . '" style="text-decoration:none">Ответить</a>';
                    echo '<div id="openModal' . $arr[$i][0] . '" class="modal">';
                    echo '<div class="modal-dialog">';
                    echo '<div class="modal-content">';
                    echo '<div class="modal-header">';
                    echo '<h3 class="modal-title">Ответить ' . $writer[1] . '</h3>';
                    echo '<a href="#close" title="Close" class="close">×</a>';
                    echo '</div>';
                    echo '<div class="modal-body">';
                    echo '<form method=post>';
                    echo '<textarea class="txt" name="messagе_text" cols="60" rows="5"></textarea>';
                    echo '<input type="submit" class="but" formaction="messenger.php?sendmessage=120&addressedto=' . $arr[$i][0] . '" value="Отправить ответ">';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    $linklike = 'messenger.php?sendlikе=1&addressedto=' . $arr[$i][0] . '';
                    $linkdis = 'messenger.php?=2&addressedto=' . $arr[$i][0] . '';
                }
                if (!$aucheck) {
                    $linklike = '#';
                    $linkdis = '#';
                }

                echo '<div class="dropdown">';
                $idofmsg = $arr[$i][0];
                $j = 0;
                $drop = "Понравилось:<br>";
                $likersq = $mysqli->query("select users.name from likesofchat, users where users.id = likesofchat.uid and likesofchat.pid = $idofmsg and likesofchat.mark=1");
                while ($liker =  $likersq->fetch_row()) {
                    $drop .= $liker[0] . '<br>';
                    $j++;
                }
                echo '<a href="' . $linklike . '" class="but">+(' . $j . ')</a>';
                echo '<div class="dropdown-content" >';
                echo $drop;
                echo '</div>';
                echo '</div>';

                echo '<div class="dropdown">';
                $idofmsg = $arr[$i][0];
                $j = 0;
                $drop = "Непонравилось:<br>";
                $likersq = $mysqli->query("select users.name from likesofchat, users where users.id = likesofchat.uid and likesofchat.pid = $idofmsg and likesofchat.mark=2 ");
                while ($liker =  $likersq->fetch_row()) {
                    $drop .= $liker[0] . '<br>';
                    $j++;
                }
                echo '<a href="' . $linkdis . '" class="but">-(' . $j . ')</a>';
                echo '<div class="dropdown-content">';
                echo $drop;
                echo '</div>';
                echo '</div><br><br>';

                if (isset($data[$arr[$i][0]])) my_sort($data, $arr[$i][0],  30);
                echo '</div>';
            }
        }

        my_sort($new_arr, 0);

        if ($aucheck) { ?>
            <br><br>
            <form method=post>
                <textarea class='txt' name='message_text' cols='150' rows='5'></textarea><br>
                <input type="submit" class="but" style="width: 80%" formaction="messenger.php?sendmessage=100" value="/>">
            </form>
        <?
        }
        ?>
</body>

</html>
<?php ob_end_flush(); ?>