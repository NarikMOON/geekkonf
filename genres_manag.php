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
    <title>Управление жанрами и типами произведений</title>
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
                    <td style="width: auto">"<? echo $role[0] ?>"</td>
                    <td style="width: auto"><? echo $u[1] ?>:</td>
                    <td style="width: auto"><? echo $u[0] ?></td>
                </tr>
            </table>
        </a>

        <?php
        echo '<br><br>';
        if (isset($_GET['do'])) {
            if ($_GET['do'] == 1) {
                $tid = $_GET['tid'];
                $newtitle = $_POST['title'];
                $mysqli->query("update types set title = '$newtitle' where id = '$tid' ");
            }
            if ($_GET['do'] == 2) {
                $tid = $_GET['tid'];
                $mysqli->query("delete from types where id = '$tid'");
                $mysqli->query("delete from affiliation where tid='$tid'");
                $mysqli->query("update media set type = 1 where type = '$tid'");
            }
            if ($_GET['do'] == 3) {
                $newtitle = $_POST['title'];
                $mysqli->query("insert into types value (NULL, '$newtitle',NOW())");
            }
        } ?>
        <table class="tab" style="vertical-align:top">
            <tr>
                <td></td>
                <td>Управление типами</td>
            </tr>
            <?
            $alltypes = $mysqli->query("select * from types");
            while ($at =  $alltypes->fetch_row()) { ?>
                <form method=post>
                    <tr>
                        <td>#<? echo $at[0] ?></td>
                        <td><input class="txt" type="text" name="title" size="30" maxlength="30" value="<? echo $at[1] ?>" />
                            <input type="submit" style="width: auto" class="but" formaction="genres_manag.php?tid=<? echo $at[0] ?>&do=1" value="Переименовать"></input>
                        </td>
                        <td><input type="submit" style="width: auto; background-color:red;" class="but" formaction="genres_manag.php?tid=<? echo $at[0] ?>&do=2" value="Удалить"></input></td>
                    </tr>
                </form>
            <?
            }
            ?>
            <form method=post>
                <tr>
                    <td>#new</td>
                    <td><input class="txt" type="text" name="title" size="30" maxlength="30" />
                        <input type="submit" style="width: auto" class="but" formaction="genres_manag.php?do=3" value="Добавить"></input>
                    </td>
                </tr>
            </form>
        </table>
        <?
        if (isset($_GET['do'])) {
            if ($_GET['do'] == 4) {
                $gid = $_GET['gid'];
                $newtitle = $_POST['title'];
                $mysqli->query("update genres set name = '$newtitle' where id = '$gid' ");
            }
            if ($_GET['do'] == 5) {
                $gid = $_GET['gid'];
                $mysqli->query("delete from genres where id = '$gid'");
                $mysqli->query("delete from affiliation where gid='$gid'");
                $mysqli->query("delete from relationships_genres where gid='$gid'");
            }
            if ($_GET['do'] == 6) {
                $newtitle = $_POST['title'];
                $mysqli->query("insert into genres value (NULL, '$newtitle',NOW())");
            }
        } ?>
        <table class="tab" style="vertical-align:top">
            <tr>
                <td></td>
                <td>Управление жанрами</td>
            </tr>
            <?
            $allgenres = $mysqli->query("select * from genres");
            while ($ag =  $allgenres->fetch_row()) { ?>
                <form method=post>
                    <tr>
                        <td>#<? echo $ag[0] ?></td>
                        <td><input class="txt" type="text" name="title" size="30" maxlength="30" value="<? echo $ag[1] ?>" />
                            <input type="submit" style="width: auto" class="but" formaction="genres_manag.php?gid=<? echo $ag[0] ?>&do=4" value="Переименовать"></input>
                        </td>
                        <td><input type="submit" style="width: auto; background-color:red;" class="but" formaction="genres_manag.php?gid=<? echo $ag[0] ?>&do=5" value="Удалить"></input></td>
                    </tr>
                </form>
            <?
            }
            ?>
            <form method=post>
                <tr>
                    <td>#new</td>
                    <td><input class="txt" type="text" name="title" size="30" maxlength="30" />
                        <input type="submit" style="width: auto" class="but" formaction="genres_manag.php?do=6" value="Добавить"></input>
                    </td>
                </tr>
            </form>
        </table><br><br>
        <table class="tab">
            <tr>
                <td></td>
                <td>Управление соотношением жанров и типов</td>
            </tr>
            <form method=post>
                <tr>
                    <td>
                        <?
                        $alltypes = $mysqli->query("select * from types"); ?>
                        <fieldset class="filter" name="types">
                            <legend>Выберите тип произведения:</legend>
                            <?
                            while ($at =  $alltypes->fetch_row()) {
                                echo '<div ><input type="radio" name="alltypes[]" value="' . $at[0] . '">';
                                echo '<label for=' . $at[0] . '>' . $at[1] . '</label>';
                                echo '</div>';
                            } ?>
                        </fieldset>
    </div>
    </div><input type="submit" style="width: auto" class="but" formaction="genres_manag.php?do=7&check=1" value="Показать отношение по типу"></input></td>
    <td>
        <?
        if (isset($_GET['do'])) {
            if ($_GET['do'] == 8) {
                $type = $_POST['alltypes'][0];
                $allgenres = $_POST['allgenres'];
                $mysqli->query("delete from affiliation where tid=$type");
                //echo  $mysqli ->error;
                $gens = "(";
                for ($i = 0; $i < count($allgenres); $i++) {
                    $gens = $gens . $allgenres[$i];
                    if ($i < count($allgenres) - 1) $gens = $gens . ', ';
                }
                $gens =  $gens . ')';
                $mysqli->query("insert into affiliation (tid, gid, log) select '$type', genres.id, NOW() from genres where genres.id in $gens");
            }
            if (($_GET['do'] == 7) || isset($_POST['alltypes'])) {
                $type = $_POST['alltypes'][0];
                $affq = $mysqli->query("select gid from affiliation where tid = $type");
                $i = 0;
                while ($row =  $affq->fetch_row()) {
                    $affiliation[$i] = $row[0];
                    $i++;
                }
            }
        }

        $allgenres = $mysqli->query("select * from genres"); ?>
        <fieldset class="filter" name="genres">
            <legend>Выберите жанры произведения:</legend>
            <?
            while ($ag =  $allgenres->fetch_row()) {
                echo '<div><input type="checkbox" name="allgenres[]" value="' . $ag[0] . (((isset($affiliation) && in_array($ag[0], $affiliation))) ? '" checked>' : '">');
                echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                echo '</div>';
            } ?>
            <input hiden style="display:none" type="checkbox" name="allgenres[]" value="0" checked>
        </fieldset>
        </div>
        </div>
    </td>
    <td><input type="submit" style="width: auto" class="but" formaction="genres_manag.php?do=8" value="Переписать"></input></td>
    </tr>
    </form>
    </table>

</body>

</html>
<?php ob_end_flush(); ?>