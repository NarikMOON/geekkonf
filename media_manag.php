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
    <title>Управление медиа контентом</title>
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
            echo '<a style="width: 9%;background-color:rgb(64, 172, 205)" href="management_panel.php" class="but">Управление</a>';
        }
        ?>
        <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
        <a style="width: 15%" href="recommendation.php" class="but">Рекомендации</a>
        <a class="btn-toggle" href="?theme=<?php echo ((isset($_SESSION['theme'])) && ($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
        <a href="userpage.php">
            <table class="tab" style="float:right">
                <tr>
                    <?php
                    $roleq = $mysqli->query("select role from accounts where id = '$u[4]'");
                    $role = $roleq->fetch_row();
                    ?>
                    <td style="width: auto">"<?php echo $role[0] ?>"</td>
                    <td style="width: auto"><?php echo $u[1] ?>: </td>
                    <td style="width: auto"><?php echo $u[0] ?></td>
                </tr>
            </table>
        </a>
    </div>
    <div display:inline-box>
        <form method=post>
            <input hidden name='check' value="1" />
            <input hidden name='login' value=<?php echo '"' . $login . '"' ?> />
            <input hidden name='password' value=<?php echo '"' . $password . '"' ?> />
            <input class="txt" type="text" name="part_of_title" size="105" maxlength="100" value=<?php if (isset($_POST['part_of_title'])) echo '"' . $_POST['part_of_title'] . '"' ?>></input>
            <br>
            <div class="dropdown">
                <a href='#' class="but">Фильтр типов</a>
                <div class="dropdown-content">
                    <?php

                    if ($mysqli->error) {
                        echo 'Error 37373737';
                        exit;
                    }
                    $alltypes = $mysqli->query("select * from types"); ?>
                    <fieldset class="filter" name="types">
                        <legend>Выберите типы для поиска:</legend>
                        <?php
                        while ($at = $alltypes->fetch_row()) {
                            echo '<div ><input type="checkbox" name="alltypes[]" value="' . $at[0] . (((isset($_POST['check']) && in_array($at[0], $_POST['alltypes'])) or (!isset($_POST['check']))) ? '" checked>' : '">');
                            echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                            echo '</div>';
                        }
                        ?>
                        <input hiden style="display:none" type="checkbox" name="alltypes[]" value="0" checked>
                    </fieldset>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="but">Фильтр жанров</a>
                <div class="dropdown-content">
                    <?php
                    $allgenres = $mysqli->query("select * from genres"); ?>
                    <fieldset class="filter" name="genres">
                        <legend>Выберите жанры для поиска:</legend>
                        <?php
                        while ($ag =  $allgenres->fetch_row()) {
                            echo '<div ><input type="checkbox" name="allgenres[]" value="' . $ag[0] . (((isset($_POST['check']) && in_array($ag[0], $_POST['allgenres'])) or (!isset($_POST['check']))) ? '" checked>' : '">');
                            echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                            echo '</div>';
                        } ?>
                        <input hiden style="display:none" type="checkbox" name="allgenres[]" value="0" checked>
                    </fieldset>
                </div>
            </div>
            <?php
            $sdate = (isset($_POST['sdate'])) ? isset($_POST['sdate']) : '';
            $edate = (isset($_POST['edate'])) ? isset($_POST['edate']) : ''; ?>
            <input type="date" class="txt" name="sdate" value="<? echo $sdate ?>"></input>-<input type="date" class="txt" name="edate" value="<? echo $edate ?>"></input>
            <input type="submit" style="width: 15%" class="but" formaction="media_manag.php" value="Поиск"></input>
        </form>
    </div>
    <?
    if (isset($_GET['do'])) {

        if ($_GET['do'] == 1) {

            if (strlen($_POST['title']) > 0) {
                $newtitle = $_POST['title'];
                $mid = $_GET['mid'];
                $mysqli->query("update media set title = '$newtitle' where id = '$mid'");
            }
        }
        if ($_GET['do'] == 2) {
            //var_dump($_FILES);
            if (!empty($_FILES) && ($_FILES['file']['type'] == "image/jpeg")) {
                $mid = $_GET['mid'];
                $tmp = $_FILES['file']['tmp_name'];
                $srs = ".\pic\\" . $mid . ".jpg";
                if (is_writable($srs)) {
                    unlink($srs);
                }
                $res = move_uploaded_file($tmp, $srs);
            }
        }
        if ($_GET['do'] == 3) {
            $mid = $_GET['mid'];
            $newtypes = $_POST['mediatypes'];
            $mysqli->query("update media set type = '$newtypes[0]' where id = '$mid'");
            $mysqli->query("delete from relationships_genres where uid='$mid'");
        }
        if ($_GET['do'] == 4) {
            $mid = $_GET['mid'];
            $mysqli->query("delete from relationships_genres where uid='$mid'");
            $newgenres = $_POST['mediagenres'];
            $gens = "(";
            for ($i = 0; $i < count($newgenres); $i++) {
                $gens = $gens . $newgenres[$i];
                if ($i < count($newgenres) - 1) $gens = $gens . ', ';
            }
            $gens =  $gens . ')';
            $mysqli->query("insert into relationships_genres (uid,gid,log) select '$mid', genres.id,NOW() from genres where genres.id in $gens");
        }
        if ($_GET['do'] == 5) {
            $mid = $_GET['mid'];
            $newdate = $_POST['date'];
            $mysqli->query("update media set year = '$newdate' where id = '$mid'");
        }
        if ($_GET['do'] == 6) {
            $mid = $_GET['mid'];
            $srs = ".\pic\\" . $mid . ".jpg";
            if (is_writable($srs)) {
                unlink($srs);
            }
            $mysqli->query("delete from media where id = '$mid'");
            $mysqli->query("delete from marks where mid = '$mid'");
            $mysqli->query("delete from comments where mid = '$mid'");
            $mysqli->query("delete from recommends where mid = '$mid'");
            $mysqli->query("delete from relationships_genres where uid='$mid'");
        }
        if ($_GET['do'] == 7) { //отклонить
            $mid = $_GET['mid'];
            $mysqli->query("update recommends set checker = 1 where mid = '$mid'");
            $addressq = $mysqli->query("select mails.address from mails,recommends where recommends.mid = $mid and recommends.uid=mails.uid");
            $address = $addressq->fetch_row();
            $titleq = $mysqli->query("select title from recommends where mid = $mid");
            $title =  $titleq->fetch_row();
            mail("geekkonf@mail.ru", 'Заявка на добавление произведения "' . $title[0] . '"', $u[0] . " отклонил заявку");
            mail($address[0], 'Заявка на добавление произведения "' . $title[0] . '"', "Ваша заявка отклонена");
            $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$address[0]'),NOW(),'Ваша заявка отклонена',NOW())");
        }
        if ($_GET['do'] == 8) { //принять
            $mid = $_GET['mid'];
            $addressq = $mysqli->query("select mails.address from mails,recommends where recommends.mid = $mid and recommends.uid=mails.uid");
            $address = $addressq->fetch_row();
            $titleq = $mysqli->query("select title from recommends where mid = $mid");
            $title = $titleq->fetch_row();
            $mysqli->query("update recommends set checker = 2 where mid = '$mid'");
            mail("geekkonf@mail.ru", 'Заявка на добавление произведения "' . $title[0] . '"', $u[0] . " принял заявку");
            mail($address[0], 'Заявка на добавление произведения "' . $title[0] . '"', "Ваша заявка принята");
            $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$address[0]'),NOW(),'Ваша заявка принята',NOW())");
        }
        if ($_GET['do'] == 9) {
            $title = $_POST['title'];
            $alltypes = $_POST['mediatypes'];
            $allgenres = $_POST['mediagenres'];
            $sdate = $_POST['date'];
            $country = $_POST['country'];
            $mysqli->query("insert into media values(NULL,'$alltypes[0]','$title','$sdate', NOW(), '$country' );");
            echo  $mysqli->error;
            $midq = $mysqli->query("select max(id) from media where type='$alltypes[0]' and title='$title' and year='$sdate'");
            echo  $mysqli->error;
            $mid =  $midq->fetch_row();
            $gens = "(";
            for ($i = 0; $i < count($allgenres); $i++) {
                $gens = $gens . $allgenres[$i];
                if ($i < count($allgenres) - 1) $gens = $gens . ', ';
            }
            $gens =  $gens . ')';
            $mysqli->query("insert into relationships_genres (uid,gid,log) select '$mid[0]', genres.id,NOW() from genres where genres.id in $gens");
            echo  $mysqli->error;
        }
        if ($_GET['do'] == 10) {
            $mid = $_GET['mid'];
            $country = $_POST['country'];
            $mysqli->query("update media set country = '$country' where id = '$mid'");
        }
    }
    echo '<br>';
    if (isset($_POST['part_of_title'])) $part_of_title = $_POST['part_of_title'];
    else $part_of_title = '';
    $query = "select * from media where id not in (select mid from recommends where checker != 2) and title like '" . $part_of_title . "%'";
    if (isset($_POST['alltypes'])) $alltypes = $_POST['alltypes'];
    else $alltypes = array();
    if (count($alltypes) == 1 && isset($_POST['check'])) {
        echo "Вы не выбрали типы<br>";
    }
    if (!empty($alltypes)) {
        $query = $query . " and type in (";
        for ($i = 0; $i < count($alltypes); $i++) {
            $query = $query . $alltypes[$i];
            if ($i < count($alltypes) - 1) $query = $query . ', ';
        }
        $query = $query . ")";
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
    if (isset($_POST['sdate'])) $sdate = $_POST['sdate'];
    //var_dump($sdate);
    if (!empty($sdate)) $query = $query . " and year > '" . $sdate . "'";
    if (isset($_POST['edate'])) $sdate = $_POST['edate'];
    if (!empty($edate)) $query = $query . " and year < '" . $edate . "'";
    //echo $query;
    $result = $mysqli->query($query);
    ?>
    <div display:inline-box position:absolute>
        <?php
        $i = 0;
        while ($row =  $result->fetch_row()) {
        ?>
            <form method=post enctype="multipart/form-data">
                <table class="tab">
                    <tr>
                        <td>#<?php echo $row[0] ?></td>
                        <td>Название:<input class="txt" type="text" name="title" size="50" maxlength="100" value="<?php echo $row[2] ?>" /></input>
                            <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<?php echo $row[0] ?>&do=1" value="Переименовать"></input>
                        </td>
                        <td><input class="txt" type="file" name="file" size="10" value="f" /></input>
                        </td>
                        <td><input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<?php echo $row[0] ?>&do=2" value="Сменить аватар"></input>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img class="media" alt="Картинки нет, но вы держитесь" src="./pic/<?php echo $row[0] ?>.jpg" />
                        </td>
                        <td>
                            <div class="dropdown">
                                <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<?php echo $row[0] ?>&do=3" value="Выбор типа"></input>
                                <div class="dropdown-content" style="position: relative;">
                                    <?php
                                    $mediatypes = $mysqli->query("select * from types"); ?>
                                    <fieldset class="filter" name="types">
                                        <legend>Выберите тип произведения:</legend>
                                        <?
                                        while ($at =  $mediatypes->fetch_row()) {
                                            echo '<div ><input type="radio" name="mediatypes[]" value="' . $at[0] . (($at[0] == $row[1]) ? '" checked>' : '">');
                                            echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="dropdown">
                                <?
                                $mediagenres = $mysqli->query("select * from genres where id in(select gid from affiliation where tid = '$row[1]' )"); ?>
                                <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo  $row[0] ?>&do=4" value="Выбор жанров"></input>
                                <div class="dropdown-content" style="position: relative;">
                                    <fieldset class="filter" name="genres">
                                        <legend>Выберите жанры:</legend>
                                        <?
                                        $thismediagenresq = $mysqli->query("select gid from relationships_genres where uid = '$row[0]' ");
                                        $j = 0;
                                        while ($tmgs =  $thismediagenresq->fetch_row()) {
                                            $tmg[$j] = $tmgs[0];
                                            $j++;
                                        }
                                        while ($ag =  $mediagenres->fetch_row()) {
                                            echo '<div ><input type="checkbox" name="mediagenres[]" value="' . $ag[0] . ((isset($tmg)) && (in_array($ag[0], $tmg)) ? '" checked>' : '">');
                                            echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                                            echo '</div>';
                                        }
                                        unset($tmg);
                                        ?>
                                        <input hiden style="display:none" type="checkbox" name="mediagenres[]" value="0" checked>
                                    </fieldset>
                                </div>
                            </div>
                        </td>
                        <td><input type="date" class="txt" name="date" value="<? echo $row[3] ?>"></input>
                            <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=5" value="Изменить дату"></input>
                        </td>
                        <td><input type="submit" style="width: auto; background-color:red;" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=6" value="Удалить"></input>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                        <td>Страна:<input class="txt" type="text" name="country" size="50" maxlength="50" value="<? echo $row[5] ?>" /></input>
                            <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=10" value="Поменять страну"></input>
                        </td>
                        <td></td>
                    </tr>
                </table>
            </form>
        <?
            $i = $i + 1;
        }
        ?>
    </div>
    <p>Найдено произведений: <? echo $i ?></p>
    <form method=post enctype="multipart/form-data">
        <table class="tab">
            <tr>
                <td>#new</td>
                <td>Название:<input class="txt" type="text" name="title" size="50" maxlength="100" /></input>
                </td>
                <td>
                </td>
            </tr>
            <tr>
                <td>
                </td>
                <td>
                    <div class="dropdown">
                        <input type="submit" style="width: auto" class="but" formaction="#" value="Выбор типа"></input>
                        <div class="dropdown-content" style="position: relative;">
                            <?
                            $mediatypes = $mysqli->query("select * from types"); ?>
                            <fieldset class="filter" name="types">
                                <legend>Выберите тип произведения:</legend>
                                <?
                                while ($at =  $mediatypes->fetch_row()) {
                                    echo '<div ><input type="radio" name="mediatypes[]" value="' . $at[0] . '">';
                                    echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </fieldset>
                        </div>
                    </div>
                    <div class="dropdown">
                        <?
                        $mediagenres = $mysqli->query("select * from genres "); ?>
                        <input type="submit" style="width: auto" class="but" formaction="#" value="Выбор жанров"></input>
                        <div class="dropdown-content" style="position: relative;">
                            <fieldset class="filter" name="genres">
                                <legend>Выберите жанры:</legend>
                                <?
                                while ($ag =  $mediagenres->fetch_row()) {
                                    echo '<div ><input type="checkbox" name="mediagenres[]" value="' . $ag[0] .  '">';
                                    echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                                    echo '</div>';
                                }
                                ?>
                                <input hiden style="display:none" type="checkbox" name="mediagenres[]" value="0" checked>
                            </fieldset>
                        </div>
                    </div>
                </td>
                <td><input type="date" class="txt" name="date"></input>
                </td>
                <td><input type="submit" style="width: auto; background-color:green;" class="but" formaction="media_manag.php?do=9" value="Добавить"></input>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                <td>Страна:<input class="txt" type="text" name="country" size="50" maxlength="100"></input>
                </td>
                <td></td>
            </tr>
        </table>
    </form>
    <br>
    <div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;    font-size: 30px; margin: 20px">
        Предложенные произведения:</div>
    <?
    $result = $mysqli->query("select * from media where id in (select mid from recommends where checker != 2)"); ?>

    <div display:inline-box position:absolute>
        <?
        $i = 0;
        while ($row =  $result->fetch_row()) {
            echo '<form  method=post enctype="multipart/form-data"><table class="tab" >';
            $infq = $mysqli->query("select * from recommends where mid = '$row[0]'");
            $inf =  $infq->fetch_row();
            echo '<tr><td>' . $inf[2] . '</td>';
            $user2 = $mysqli->query("select * from users where id='$inf[1]'");
            $u2 =  $user2->fetch_row();
            $roleq = $mysqli->query("select role from accounts where id = '$u2[4]'");

            $role =  $roleq->fetch_row();
            echo '<td style="width: auto">"' . $role[0] . '"</td>';
            echo '<td style="width: auto">' . $u2[1] . ':</td>';
            $checkq = $mysqli->query("select checker from recommends where mid = '$row[0]'");
            $check =  $checkq->fetch_row();
            if ($check[0] == 2) {
                echo '<td>Одобрен</td>';
            } else if ($check[0] == 1) {
                echo '<td>Отклонен</td>';
            } else {
                echo '<td>Новый</td>';
            } ?>
            </tr>
            <tr>
                <td>#<? echo $row[0] ?></td>
                <td>Название:<input class="txt" type="text" name="title" size="50" maxlength="100" value="<? echo $row[2] ?>" /></input>
                    <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=1" value="Переименовать"></input>
                </td>
                <td><input class="txt" type="file" name="file" size="10" value="f" /></input>
                </td>
                <td><input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=2" value="Сменить аватар"></input>
                </td>
            </tr>
            <tr>
                <td>
                    <img class="media" alt="Картинки нет, но вы держитесь" src="./pic/<? echo $row[0] ?>.jpg" />
                </td>
                <td>
                    <div class="dropdown">
                        <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=3" value="Выбор типа"></input>
                        <div class="dropdown-content" style="position: relative;">
                            <?
                            $mediatypes = $mysqli->query("select * from types"); ?>
                            <fieldset class="filter" name="types">
                                <legend>Выберите тип произведения:</legend>
                                <?
                                while ($at =  $mediatypes->fetch_row()) {
                                    echo '<div ><input type="radio" name="mediatypes[]" value="' . $at[0] . (($at[0] == $row[1]) ? '" checked>' : '">');
                                    echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                                    echo '</div>';
                                } ?>
                            </fieldset>
                        </div>
                    </div>
                    <div class="dropdown">
                        <?
                        $mediagenres = $mysqli->query("select * from genres where id in(select gid from affiliation where tid = '$row[1]'  )"); ?>
                        <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=4" value="Выбор жанров"></input>
                        <div class="dropdown-content" style="position: relative;">
                            <fieldset class="filter" name="genres">
                                <legend>Выберите жанры:</legend>
                                <?
                                $thismediagenresq = $mysqli->query("select gid from relationships_genres where uid = '$row[0]' ");
                                $j = 0;
                                while ($tmgs =  $thismediagenresq->fetch_row()) {
                                    $tmg[$j] = $tmgs[0];
                                    $j++;
                                }
                                while ($ag =  $mediagenres->fetch_row()) {
                                    echo '<div ><input type="checkbox" name="mediagenres[]" value="' . $ag[0] . ((isset($tmg)) && (in_array($ag[0], $tmg)) ? '" checked>' : '">');
                                    echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                                    echo '</div>';
                                }
                                ?>
                                <input hiden style="display:none" type="checkbox" name="mediagenres[]" value="0" checked>
                            </fieldset>
                        </div>
                    </div>
                </td>
                <td><input type="date" class="txt" name="date" value="<? echo $row[3] ?>"></input>
                    <input type="submit" style="width: auto" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=5" value="Изменить дату"></input>
                </td>
                <td><input type="submit" style="width: auto; background-color:red;" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=7" value="Отклонить"></input>
                    <input type="submit" style="width: auto; background-color:green;" class="but" formaction="media_manag.php?mid=<? echo $row[0] ?>&do=8" value="Принять"></input>
                </td>
            </tr>
            </table>
            </form>
        <?
            $i = $i + 1;
        }
        ?>
        <h1>Динамика выхода произведений</h1>
        <?php
        $graf1q = $mysqli->query('select CONCAT("new Date(",YEAR(media.year),",0,1)"), count(media.id) from  media  group by year(media.year) order by media.year');

        echo  $mysqli->error;
        $arr1 = array();
        $arr2 = array();
        $arr4 = array();

        while ($graf1 =  $graf1q->fetch_row()) {
            $str = '[' . $graf1[0] . ',' . $graf1[1] . '],';
            array_push($arr1, $str);
            array_push($arr2, $graf1[1]);
            //var_dump($graf1);echo '<br>';
        }
        $str = implode("", $arr1);

        $table3 = '<table border="1" class="tab1">';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Период</th>';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Фактические значения</th>';
        $table3 .= '<th colspan = 2 style="text-align: center;">Скользящие средние</th>';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Взвешенная скользящая средняя l=5</th>';
        $table3 .= '<tr>';
        $table3 .= '<td style="text-align: center;">l=3</td>';
        $table3 .= '<td style="text-align: center;">l=7</td>';
        $table3 .= '</tr>';
        $table3 .= '<tr><td>' . (1) . '</td><td>' . ($arr2[0]) . '</td>';
        $table3 .= '<td>-</td>';
        $table3 .= '<td>-</td>';
        $table3 .= '<td>-</td></tr>';
        $str1 = '[' . (1) . ',' . $arr2[0] . ',';
        $str1 .= 'undefined,';
        $str1 .=  'undefined,';
        $str1 .= 'undefined],';
        //echo count($arr2);
        array_push($arr4, $str1);
        for ($j = 1; $j < count($arr2); $j++) {
            $str1 = '[' . ($j + 1) . ',' . $arr2[$j] . ',';
            $table3 .= '<tr><td>' . ($j + 1) . '</td><td>' . ($arr2[$j]) . '</td>';
            if (($j > 0) and ($j < count($arr2) - 1)) {
                $table3 .= '<td>' . number_format(($arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1]) / 3, 2, '.', ',') . '</td>';
                $str1 .= number_format(($arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1]) / 3, 2, '.', ',') . ',';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined,';
            }
            if (($j > 2) and ($j < count($arr2) - 3)) {
                $table3 .= '<td>' . number_format(($arr2[$j - 3] + $arr2[$j - 2] + $arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1] + $arr2[$j + 2] + $arr2[$j + 3]) / 7, 2, '.', ',') . '</td>';
                $str1 .= number_format(($arr2[$j - 3] + $arr2[$j - 2] + $arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1] + $arr2[$j + 2] + $arr2[$j + 3]) / 7, 2, '.', ',') . ',';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined,';
            }
            if (($j > 1) and ($j < count($arr2) - 2)) {
                $table3 .= '<td>' .   number_format((-3 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 17 * $arr2[$j] + 12 * $arr2[$j + 1] - 3 * $arr2[$j + 2]) / 35, 2, '.', ',') . '</td>';
                $str1 .=            number_format((-3 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 17 * $arr2[$j] + 12 * $arr2[$j + 1] - 3 * $arr2[$j + 2]) / 35, 2, '.', ',') . '],';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined],';
            }
            array_push($arr4, $str1);
            $table3 .= '</tr>';
        }

        $str1 = implode("", $arr4);
        //echo $str1;
        $table3 .= '</table>';
        //echo $str1;
        //echo $str;

        echo '<h1>Задание 1</h1>';
        echo $table3;

        ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Дата', 'Количество', ],
                    <?php echo $str ?>
                ]);

                var options = {
                    title: 'Динамика выхода произведений',
                    curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                    trendlines: {
                        0: {}
                    }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }
        </script>

        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Месяц', 'Фактические значения', 'l3', 'l7', 'l5w'],
                    <?php echo $str1 ?>
                ]);

                var options = {
                    title: 'Скользящие средние',
                    curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                };
                var chart = new google.visualization.LineChart(document.getElementById('curve_chart2'));

                chart.draw(data, options);
            }
        </script>
        <div id="curve_chart2" style="width: 900px; height: 500px"></div>
        <?php

        $arr4 = array();
        $table3 = '<table border="1" class="tab1">';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Период</th>';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Фактические значения</th>';
        $table3 .= '<th colspan = 2 style="text-align: center;">Скользящие средние</th>';
        $table3 .= '<th rowspan = 2 style="text-align: center;">Взвешенная скользящая средняя l=5</th>';
        $table3 .= '<tr>';
        $table3 .= '<td style="text-align: center;">l=3</td>';
        $table3 .= '<td style="text-align: center;">l=7</td>';
        $table3 .= '</tr>';
        $table3 .= '<tr><td>' . (1) . '</td><td>' . ($arr2[0]) . '</td>';
        $table3 .= '<td style="font-weight: bold;">' . number_format((5 * $arr2[0] + 2 * $arr2[1] - $arr2[2]) / 6, 2, '.', ',') . '</td>';
        $table3 .= '<td style="font-weight: bold;">' . number_format((39 * $arr2[0] + 8 * $arr2[1] - 4 * $arr2[2] - 4 * $arr2[3] + 1 * $arr2[4] + 4 * $arr2[5] - 2 * $arr2[6])  / 42, 2, '.', ',') . '</td>';
        $table3 .= '<td style="font-weight: bold;">' . number_format((31 * $arr2[0] + 9 * $arr2[1] - 3 * $arr2[2] - 5 * $arr2[3] + 3 * $arr2[4]) / 35, 2, '.', ',') . '</td></tr>';
        $str1 = '[' . (1) . ',' . $arr2[0] . ',';
        $str1 .= number_format((5 * $arr2[0] + 2 * $arr2[1] - $arr2[2]) / 6, 2, '.', ',') . ',';
        $str1 .= number_format((39 * $arr2[0] + 8 * $arr2[1] - 4 * $arr2[2] - 4 * $arr2[3] + 1 * $arr2[4] + 4 * $arr2[5] - 2 * $arr2[6]) / 42, 2, '.', ',') . ',';
        $str1 .= number_format((31 * $arr2[0] + 9 * $arr2[1] - 3 * $arr2[2] - 5 * $arr2[3] + 3 * $arr2[4]) / 35, 2, '.', ',') . '],';
        //echo count($arr2);
        array_push($arr4, $str1);
        for ($j = 1; $j < count($arr2); $j++) {

            $str1 = '[' . ($j + 1) . ',' . $arr2[$j] . ',';
            $table3 .= '<tr><td>' . ($j + 1) . '</td><td>' . ($arr2[$j]) . '</td>';
            if (($j > 0) and ($j < count($arr2) - 1)) {
                $table3 .= '<td>' . number_format(($arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1]) / 3, 2, '.', ',') . '</td>';
                $str1 .= number_format(($arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1]) / 3, 2, '.', ',') . ',';
            } else if ($j == count($arr2) - 1) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((-$arr2[$j - 2] + 2 * $arr2[$j - 1] + 5 * $arr2[$j]) / 6, 2, '.', ',') . '</td>';
                $str1 .= number_format((-$arr2[$j - 2] + 2 * $arr2[$j - 1] + 5 * $arr2[$j]) / 6, 2, '.', ',') . ',';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined,';
            }
            if (($j > 2) and ($j < count($arr2) - 3)) {
                $table3 .= '<td>' . number_format(($arr2[$j - 3] + $arr2[$j - 2] + $arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1] + $arr2[$j + 2] + $arr2[$j + 3]) / 7, 2, '.', ',') . '</td>';
                $str1 .= number_format(($arr2[$j - 3] + $arr2[$j - 2] + $arr2[$j - 1] + $arr2[$j] + $arr2[$j + 1] + $arr2[$j + 2] + $arr2[$j + 3]) / 7, 2, '.', ',') . ',';
            } else if ($j == count($arr2) - 3) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((1 * $arr2[$j - 4] - 4 * $arr2[$j - 3] + 2 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 19 * $arr2[$j] + 16 * $arr2[$j + 1] - 4 * $arr2[$j + 2]) / 42, 2, '.', ',') . '</td>';
                $str1 .=            number_format((1 * $arr2[$j - 4] - 4 * $arr2[$j - 3] + 2 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 19 * $arr2[$j] + 16 * $arr2[$j + 1] - 4 * $arr2[$j + 2]) / 42, 2, '.', ',') . ',';
            } else if ($j == count($arr2) - 2) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((4 * $arr2[$j - 5] - 7 * $arr2[$j - 4] - 4 * $arr2[$j - 3] + 6 * $arr2[$j - 2] + 16 * $arr2[$j - 1] + 19 * $arr2[$j] + 9 * $arr2[$j + 1]) / 42, 2, '.', ',') . '</td>';
                $str1 .=            number_format((4 * $arr2[$j - 5] - 7 * $arr2[$j - 4] - 4 * $arr2[$j - 3] + 6 * $arr2[$j - 2] + 16 * $arr2[$j - 1] + 19 * $arr2[$j] + 9 * $arr2[$j + 1]) / 42, 2, '.', ',') . ',';
            } else if ($j == count($arr2) - 1) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((2 * $arr2[$j - 6] + 4 * $arr2[$j - 5] + 1 * $arr2[$j - 4] - 4 * $arr2[$j - 3] - 4 * $arr2[$j - 2] + 4 * $arr2[$j - 1] + 39 * $arr2[$j]) / 42, 2, '.', ',') . '</td>';
                $str1 .=            number_format((2 * $arr2[$j - 6] + 4 * $arr2[$j - 5] + 1 * $arr2[$j - 4] - 4 * $arr2[$j - 3] - 4 * $arr2[$j - 2] + 4 * $arr2[$j - 1] + 39 * $arr2[$j]) / 42, 2, '.', ',') . ',';
            } else if ($j == 1) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((8 * $arr2[0] + 19 * $arr2[1] + 16 * $arr2[2] + 6 * $arr2[3] - 4 * $arr2[4] - 7 * $arr2[5] + 4 * $arr2[6]) / 42, 2, '.', ',') . '</td>';
                $str1 .=            number_format((8 * $arr2[0] + 19 * $arr2[1] + 16 * $arr2[2] + 6 * $arr2[3] - 4 * $arr2[4] - 7 * $arr2[5] + 4 * $arr2[6])  / 42, 2, '.', ',') . ',';
            } else if ($j == 2) {
                $table3 .= '<td style="font-weight: bold;">' . number_format((-4 * $arr2[0] + 16 * $arr2[1] + 19 * $arr2[2] + 12 * $arr2[3] + 2 * $arr2[4] - 4 * $arr2[5] + 1 * $arr2[6]) / 42, 2, '.', ',') . '</td>';
                $str1 .=            number_format((-4 * $arr2[0] + 16 * $arr2[1] + 19 * $arr2[2] + 12 * $arr2[3] + 2 * $arr2[4] - 4 * $arr2[5] + 1 * $arr2[6]) / 42, 2, '.', ',') . ',';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined,';
            }
            if (($j > 1) and ($j < count($arr2) - 2)) {
                $table3 .= '<td>' .   number_format((-3 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 17 * $arr2[$j] + 12 * $arr2[$j + 1] - 3 * $arr2[$j + 2]) / 35, 2, '.', ',') . '</td>';
                $str1 .=            number_format((-3 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 17 * $arr2[$j] + 12 * $arr2[$j + 1] - 3 * $arr2[$j + 2]) / 35, 2, '.', ',') . '],';
            } else if ($j == count($arr2) - 2) {
                $table3 .= '<td style="font-weight: bold;">' .   number_format((-5 * $arr2[$j - 3] + 6 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 13 * $arr2[$j - 1] - 9 * $arr2[$j + 1]) / 35, 2, '.', ',') . '</td>';
                $str1 .=            number_format((-5 * $arr2[$j - 3] + 6 * $arr2[$j - 2] + 12 * $arr2[$j - 1] + 13 * $arr2[$j - 1] - 9 * $arr2[$j + 1]) / 35, 2, '.', ',') . '],';
            } else if ($j == count($arr2) - 1) {
                $table3 .= '<td style="font-weight: bold;">' .   number_format((3 * $arr2[$j - 4] - 5 * $arr2[$j - 3] - 3 * $arr2[$j - 2] + 9 * $arr2[$j - 1] + 31 * $arr2[$j]) / 35, 2, '.', ',') . '</td>';
                $str1 .=            number_format((3 * $arr2[$j - 4] - 5 * $arr2[$j - 3] - 3 * $arr2[$j - 2] + 9 * $arr2[$j - 1] + 31 * $arr2[$j]) / 35, 2, '.', ',') . '],';
            } elseif ($j ==  1) {
                $table3 .= '<td style="font-weight: bold;">' .   number_format((9 * $arr2[0] + 13 * $arr2[1] + 12 * $arr2[2] + 6 * $arr2[3] - 5 * $arr2[4]) / 35, 2, '.', ',') . '</td>';
                $str1 .=              number_format((9 * $arr2[0] + 13 * $arr2[1] + 12 * $arr2[2] + 6 * $arr2[3] - 5 * $arr2[4]) / 35, 2, '.', ',') . '],';
            } else {
                $table3 .= '<td>-</td>';
                $str1 .= 'undefined],';
            }
            array_push($arr4, $str1);
            $table3 .= '</tr>';
        }
        $progn3 = number_format(($arr2[count($arr2) - 3] + $arr2[count($arr2) - 2] + $arr2[count($arr2) - 1]) / 3 + ($arr2[count($arr2) - 1] - $arr2[count($arr2) - 2]) / 3, 2, '.', ',');
        $progn5 = number_format((3 * $arr2[count($arr2) - 5] - 5 * $arr2[count($arr2) - 4] - 3 * $arr2[count($arr2) - 3] + 9 * $arr2[count($arr2) - 2] + 31 * $arr2[count($arr2) - 1]) / 5 + ($arr2[count($arr2) - 1] - $arr2[count($arr2) - 2]) / 5, 2, '.', ',');
        $progn7 = number_format(($arr2[count($arr2) - 7] + $arr2[count($arr2) - 6] + $arr2[count($arr2) - 5] + $arr2[count($arr2) - 4] + $arr2[count($arr2) - 3] + $arr2[count($arr2) - 2] + $arr2[count($arr2) - 1]) / 7 + ($arr2[count($arr2) - 1] - $arr2[count($arr2) - 2]) / 7, 2, '.', ',');
        $str1 = '[' . (count($arr2) + 1) . ', undefined ,' . $progn3 . ',' . $progn7 . ',' . $progn5 . ']';
        array_push($arr4, $str1);
        $str1 = implode("", $arr4);
        //echo $str1;
        $table3 .= '<tr><td style="font-weight: bold;">Прогноз:</td><td></td><td style="font-weight: bold;">' . $progn3 . '</td><td style="font-weight: bold;">' . $progn7 . '</td><td style="font-weight: bold;">' . $progn5 . '</td></tr></table>';
        //echo $str1;
        //echo $str;
        echo '<h1>Задание 2+3</h1>';
        echo $table3;

        ?>

        <?php
        echo '<br><h1>Лабораторная работа 8.1.2</h1><br>';
        require_once('fun.php');
        HipoTreng($arr2);

        echo '<h1>Лабораторная работа 8.2.2</h1><br>';
        $var = lr82($arr2);
        echo $var['table'];
        echo '<br>Функции графиков:';
        echo '<br>' . $var['expr1'];
        echo '<br>' . $var['expr2'];
        echo '<br>' . $var['expr3'];
        ?>


        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    <?php echo $var['data'] ?>
                ]);
                var options = {
                    title: 'Модели',
                    curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                };
                var chart = new google.visualization.LineChart(document.getElementById('8curve_chart1'));
                chart.draw(data, options);
            }
        </script>
        <div id="8curve_chart1" style="width: 900px; height: 500px"></div>

        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Месяц', 'Фактические значения', 'l3', 'l7', 'l5w'],
                    <?php echo $str1 ?>
                ]);
                var options = {
                    title: 'Востановление краевых значений и прогноз',
                    curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                };
                var chart = new google.visualization.LineChart(document.getElementById('curve_chart3'));
                chart.draw(data, options);
            }
        </script>
        <div id="curve_chart3" style="width: 900px; height: 500px"></div>


        <div id="curve_chart" style="width: 900px; height: 500px"></div>
        <h4>По автоматически отрисованной линии тренда видно, что динамика выхода произведений нарастает - произведений выходит всё больше и больше с течением времени.</h4>

        <?php
        echo '<h1>ЛР9.2</h1><br>' . $var['lr9'];
        $typesq = $mysqli->query('select types.id, types.title from types, media where media.type=types.id group by media.type having count(media.id)>0 ');

        //$arr1 = array(array(array()));
        $typesss = array();
        $datesss = array();
        $trends = '';
        $g = 0;

        $arr2 = array(array());
        while ($types =  $typesq->fetch_row()) {
            $dates = $mysqli->query("select count(id), CONCAT('new Date(',year(year), ',',month(year),')' ) from media where type=$types[0] group by year(year) order by year(year)"); //CONCAT('new Date(',year(year), ',',month(year),')' )

            array_push($typesss, $types[1]);
            while ($medias =  $dates->fetch_row()) {
                array_push($datesss, $medias[1]);
                $arr2[$types[1]][$medias[1]] = $medias[0];
            }
            $trends .= $g . ':{},';
            $g++;
        }




        echo '<h1>Лабораторная работа 8.1.3/.2.3</h1><br>';



        //var_dump($arr2);echo '1<br>';
        //var_dump($typesss);echo '2<br>';
        //var_dump($datesss);echo '3<br>';
        sort($datesss);
        $newarray = array();
        $str = "['Дата',";
        for ($k = 0; $k < count($typesss); $k++) {
            $str .= "'" . $typesss[$k] . "',";
            echo "Для типа произведения " . $typesss[$k] . ': ';
            if (count($arr2[$typesss[$k]]) > 1) {
                HipoTreng($arr2[$typesss[$k]]);
                $var = lr82($arr2[$typesss[$k]]);
                echo '<br>';
                echo $var['table'];
                echo '<br>Функции графиков:';
                echo '<br>' . $var['expr1'];
                echo '<br>' . $var['expr2'];
                echo '<br>' . $var['expr3']; ?>

                <script type="text/javascript">
                    google.charts.load('current', {
                        'packages': ['corechart']
                    });
                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                        var data = google.visualization.arrayToDataTable([
                            <?php echo $var['data'] ?>
                        ]);
                        var options = {
                            title: 'Модели',
                            curveType: 'function',
                            legend: {
                                position: 'bottom'
                            },
                        };
                        var chart = new google.visualization.LineChart(document.getElementById('8curve_chart1' + <?php echo $k; ?>));
                        chart.draw(data, options);
                    }
                </script>
                <div id="8curve_chart1<?php echo $k; ?>" style="width: 900px; height: 500px"></div>
        <?php
                echo '<h1>ЛР9.3.*</h1><br>' . $var['lr9'];
            } else {
                echo '<br>Недостаточно данных для работы<br>';
            }
            echo '<br>';
        }
        $str .= "],";
        //echo $str;
        array_push($newarray, $str);
        for ($j = 0; $j < count($datesss); $j++) {
            $str = '[' . $datesss[$j] . ',';
            for ($k = 0; $k < count($typesss); $k++) {
                if (isset($arr2[$typesss[$k]][$datesss[$j]])) {
                    $count = $arr2[$typesss[$k]][$datesss[$j]];
                    $str .= $count . ",";
                } else {
                    $str .= " undefined ,";
                }
            }
            $str .= "],";
            array_push($newarray, $str);
        }
        $str = implode("", $newarray);

        //$str = implode("", $arr1);
        //echo $str;

        ?>
        </script>
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    <?php echo $str ?>
                ]);

                var options = {
                    title: 'Динамика выхода произведений по типам',
                    //curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                    trendlines: {
                        <?php echo $trends; ?>
                    }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart1'));

                chart.draw(data, options);
            }
        </script>
        <h1>Динамика выхода произведений по типам</h1>
        <div id="curve_chart1" style="width: 900px; height: 500px"></div>
        <h4>По отрисованным автоматически линиям трендов для каждой категории можно сделать выводы, что тренд на выход фильмов растет, а тренды на книги и аниме спадают. Но это связано с нехваткой заполнения произведениями базы данных, из-за чего также линий тренда у других категорий не видно</h4>
</body>

</html>
<?php ob_end_flush(); ?>