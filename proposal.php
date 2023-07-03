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
    <title>Предложение</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <?php
        if (isset($_SESSION['login'])) $login = $_SESSION['login'];
        if (isset($_SESSION['password'])) $password = $_SESSION['password'];
        if (!isset($login) || !isset($password)) {
            echo 'Вы ввели не все необходимые сведения!';
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
            echo '<a style="width: 9%" href="management_panel.php" class="but">Управление</a>';
        } ?>
        <a style="width: 9%; background-color:rgb(64, 172, 205)" href="#" class="but">Предложение</a>
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
        <?php
        ?>
    </div>
    <div display:inline-box>
        <form method=post>
            <input hidden name='check' value="1" />
            <input hidden name='login' value=<?php echo '"' . $login . '"' ?> />
            <input hidden name='password' value=<?php echo '"' . $password . '"' ?> />
            <div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;    font-size: 30px; margin: 20px"> Введите название произведения:</div>
            <input class="txt" type="text" name="part_of_title" size="105" maxlength="100" value=<?php if (isset($_POST['part_of_title'])) echo '"' . $_POST['part_of_title'] . '"' ?>></input>
            <br>
            <div class="dropdown">
                <a href='#' class="but">Выберите тип</a>
                <div class="dropdown-content">
                    <?php

                    if ($mysqli->errno) {
                        echo 'Error 37373737';
                        exit;
                    }
                    $alltypes = $mysqli->query("select * from types"); ?>
                    <fieldset class="filter" name="types">
                        <legend>Выберите тип произведения:</legend>
                        <?
                        while ($at =  $alltypes->fetch_row()) {
                            echo '<div ><input type="radio" name="alltypes[]" value="' . $at[0] . (((isset($_POST['check']) && in_array($at[0], $_POST['alltypes'])) or (!isset($_POST['check']))) ? '" checked>' : '">');
                            echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                            echo '</div>';
                        }
                        ?>
                    </fieldset>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="but">Выберите жанры</a>
                <div class="dropdown-content">
                    <?
                    $allgenres = $mysqli->query("select * from genres"); ?>
                    <fieldset class="filter" name="genres">
                        <legend>Выберите жанры произведения:</legend>
                        <?
                        while ($ag =  $allgenres->fetch_row()) {
                            echo '<div><input type="checkbox" name="allgenres[]" value="' . $ag[0] . (((isset($_POST['check']) && in_array($ag[0], $_POST['allgenres']))) ? '" checked>' : '">');
                            echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                            echo '</div>';
                        } ?>
                        <input hiden style="display:none" type="checkbox" name="allgenres[]" value="0" checked>
                    </fieldset>
                </div>
            </div>
            Дата релиза:<input type="date" class="txt" name="date" value="<? echo ((isset($_POST['date'])) ? $_POST['date'] : '') ?>"></input>
            <input type="submit" style="width: 15%" class="but" formaction="proposal.php" value="Проверка на наличие"></input>
            <input type="submit" style="width: 15%" class="but" formaction="proposal.php?addmedia=1" value="Отправить заявку"></input>
        </form>
    </div>
    <br>
    <?
    if (isset($_POST['part_of_title'])) $part_of_title = $_POST['part_of_title'];
    else $part_of_title = '';
    $query = "select * from media where id not in (select mid from recommends where checker != 2) and title like '" . $part_of_title . "%'";
    if (isset($_POST['alltypes'])) $alltypes = $_POST['alltypes'];
    else $alltypes = array();
    if (!empty($alltypes)) {
        $query = $query . " and type = '" . $alltypes[0] . "' ";
    }
    if (isset($_POST['allgenres'])) $allgenres = $_POST['allgenres'];
    else $allgenres = array();
    if (count($allgenres) == 1 && isset($_POST['check'])) {
        echo "Вы не выбрали жанры<br>";
    }
    if (!empty($allgenres)) {
        $query = $query . " and id in (select uid from relationships_genres where gid in(";
        for ($i = 0; $i < count($allgenres); $i++) {
            $query = $query . $allgenres[$i];
            if ($i < count($allgenres) - 1) $query = $query . ', ';
        }
        $query = $query . "))";
    }
    if (isset($_POST['date'])) $sdate = $_POST['date'];
    if (!empty($sdate)) $query = $query . " and year = '" . $sdate . "'";
    $result = $mysqli->query($query);
    echo '<div display:inline-box position:absolute>';
    $i = 0;
    if ($result->num_rows > 0) {
        while ($row =  $result->fetch_row()) {
            $url = 'media.php?mid=' . ($row[0]);
            echo '<a class="media" href=' . $url . ' ><strong>';
            echo '<input hidden name="check" value="1"/>';
            echo '<img class="media" alt="Картинки нет, но вы держитесь" src="./pic/' . $row[0] . '.jpg"/>';
            echo stripslashes($row[2]);
            $type = $mysqli->query("select title from types where id=" . ($row[1]));
            $tr =  $type->fetch_row();
            echo '</strong><br />';
            echo stripslashes($tr[0]);
            echo '</strong><br />Жанры:';
            $genres = $mysqli->query("select genres.name from relationships_genres , genres  where genres.id=relationships_genres.gid and relationships_genres.uid=" . ($row[0]));
            while ($gr =  $genres->fetch_row()) {
                echo ' ' . $gr[0];
            }
            echo '<br />Дата выхода: ';
            echo stripslashes($row[3]);
            echo '<br/>Страна: ' . $row[5];
            echo '</p>';
            echo '</a>';
            $i = $i + 1;
        }
        echo '</div><p>Найдено произведений: ' . $i . '</p>';
    }

    if (isset($_GET['addmedia'])) {
        if (($_GET['addmedia'] == "1") && (strlen($_POST['part_of_title']) > 0) && (!empty($sdate))) {

            $title = $_POST['part_of_title'];
            $addressq = $mysqli->query("select address from mails where uid = $u[0]");
            $address =  $addressq->fetch_row();
            $mysqli->query("insert into media values(NULL,'$alltypes[0]','$title','$sdate', NOW(),NULL)");
            echo $mysqli->error;
            $midq = $mysqli->query("select max(id) from media where type='$alltypes[0]' and title='$title' and year='$sdate'");
            $mid =  $midq->fetch_row();
            $gens = "(";
            for ($i = 0; $i < count($allgenres); $i++) {
                $gens = $gens . $allgenres[$i];
                if ($i < count($allgenres) - 1) $gens = $gens . ', ';
            }
            $gens =  $gens . ')';
            $mysqli->query("insert into relationships_genres (uid,gid,log) select '$mid[0]', genres.id, NOW() from genres where genres.id in $gens");
            echo $mysqli->error;

            $mysqli->query("insert into recommends values (NULL, $u[0] , now(), '$title','$mid[0]', 0, NOW() )");
            echo $mysqli->error;

            mail("geekkonf@mail.ru", 'Заявка на добавление произведения "' . $title . '"', "Пришла заявка от " . $address[0]);
            mail($address[0], 'Заявка на добавление произведения "' . $title . '"', "Ваша заявка отправлена на рассмотрение");
            $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$address[0]'),NOW(),'Ваша заявка отправлена на рассмотрение', NOW())");
        }
        if (($_GET['addmedia'] == "1") && !((strlen($_POST['part_of_title']) > 0) && (!empty($sdate)))) {
            echo 'Запись не добавлена, заполните все поля!';
        }
        if ($_GET['addmedia'] == "2") {
            $mid = $_GET['mid'];

            $addressq = $mysqli->query("select address from mails where uid = $u[0]");
            $address =  $addressq->fetch_row();
            $titleq = $mysqli->query("select title from recommends where mid = $mid");

            $title = $titleq->fetch_row();

            $checkq = $mysqli->query("select checker from recommends where mid = '$row[0]'");
            $check =  $checkq->fetch_row();
            if ($check[0] != 2) {
                $mysqli->query("delete from media where id='$mid'");
                $mysqli->query("delete from relationships_genres where uid='$mid'");
            }
            $mysqli->query("delete from recommends where mid='$mid'");
            mail("geekkonf@mail.ru", 'Заявка на добавление произведения "' . $title[0] . '"', "Отклонение заявки " . $address[0]);
            mail($address[0], 'Заявка на добавление произведения "' . $title[0] . '"', "Вы удалили заявку");

            $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$address[0]'),NOW(),'Вы удалили заявку', NOW())");
        }
    }
    $i = 0;
    $recsq = $mysqli->query("select * from media where id in (select mid from recommends where uid = '$u[0]')");
    if ($recsq->num_rows > 0) {
        echo '<br><div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;    font-size: 30px; margin: 20px">';
        echo 'Предложенные вами произведения:</div>';
        while ($row =  $recsq->fetch_row()) {
            $url = 'proposal.php?mid=' . ($row[0]) . '&addmedia=2';
            echo '<a class="media" href=' . $url . ' ><strong>';
            echo '<input hidden name="check" value="1"/>';
            echo '<div style="color: rgb(255,0,0); background-color:rgb(0,0,0); ">Удалить запись</div>';
            echo stripslashes($row[2]);
            $type = $mysqli->query("select title from types where id=" . ($row[1]));
            $tr =  $type->fetch_row();
            echo '</strong><br />';
            echo '<br /></strong><br />Жанры:';
            $genres = $mysqli->query("select genres.name from relationships_genres , genres  where genres.id=relationships_genres.gid and relationships_genres.uid=" . ($row[0]));
            while ($gr =  $genres->fetch_row()) {
                echo ' ' . $gr[0];
            }
            echo '<br />Дата выхода: ';
            echo stripslashes($row[3]);
            $checkq = $mysqli->query("select checker from recommends where mid = '$row[0]'");
            $check =  $checkq->fetch_row();
            if ($check[0] == 2) {
                echo '<div style="color: black; background-color:green; ">Одобрен</div>';
            } else if ($check[0] == 1) {
                echo '<div style="color: black; background-color:red; ">Отклонен</div>';
            } else {
                echo '<div style="color: black; background-color:blue; ">Рассматривается</div>';
            }
            echo '</p>';
            echo '</a>';
            $i = $i + 1;
        }
        echo '</div><p>Найдено произведений: ' . $i . '</p>';
    }

    ?>
</body>

</html>
<?php ob_end_flush(); ?>