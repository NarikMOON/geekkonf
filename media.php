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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <title>Произведение</title>
</head>

<body <?php if (isset($_SESSION['theme']))echo $_SESSION['theme']; ?>>
    <div class="header">Geek Konf</div>
    <div display:inline-box>
        <a style="width: 9%;" href="authorization.php" class="but">Авторизация</a>
        <?php
        $mid = $_GET['mid'];
        if (isset($_SESSION['login']))$login = $_SESSION['login'];
        if (isset($_SESSION['password']))$password = $_SESSION['password'];
        if (!isset($login) || !isset($password)) {
            echo 'Вы ввели не все необходимые сведения!';
            exit;
        }
        echo '<a style="width: 9%" href="recommendation.php" class="but">Рекомендации</a>';?>
        <a class="btn-toggle" href="?theme= <? echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
        <?
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');
        $user = $mysqli->query("select * from users where login ='$login' and password = sha1('$password')");
        $u = $user->fetch_row();
        if ($u[0] < 1) {
            echo '<h3>Логин и пароль не верны!</h3>';
            exit;
        }?>
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
    </div><br>

    <?php
    $mediaq = $mysqli->query("select * from media where id='$mid'");
    $media =  $mediaq->fetch_row();?>
    <div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;    font-size: 30px; margin: 50px">
    <img class="media" style="height: auto;width: 30%;vertical-align: middle; position: relative; margin: 0 5%" alt="Картинки нет, но вы держитесь" src="./pic/<? echo $media[0] ?>.jpg"/><br>
    <?
    echo stripslashes($media[2]);
    $type = $mysqli->query("select title from types where id=" . ($media[1]));
    $tr =  $type->fetch_row();
    echo '<br/><strong>';
    echo stripslashes($tr[0]);
    echo '<br/>Жанры:</strong>';
    $genres = $mysqli->query("select genres.name from relationships_genres , genres  where genres.id=relationships_genres.gid and relationships_genres.uid=" . ($media[0]));
    while ($gr =  $genres->fetch_row()) {
        echo ' ' . $gr[0];
    }
    echo '<br/>Дата выхода: ';
    echo stripslashes($media[3]);
    echo '<br/>Страна: ' . $media[5];
    echo '</div>';
    $markq = $mysqli->query("select value from marks where uid='$u[0]' and mid='$media[0]'");
    $mark =  $markq->fetch_row();
    if (isset($_GET['mark'])) {
        $m = $_GET['mark'];
        if (!$mark) {
            $mysqli->query("insert into marks values (NULL,'$u[0]','$media[0]','$m',NOW())");
        }
        if (count($mark) > 0) {
            $mysqli->query("update marks set value='$m' where uid='$u[0]' and mid='$media[0]'");
        }
    }
    $markq = $mysqli->query("select value from marks where uid='$u[0]' and mid='$media[0]'");
    $mark =  $markq->fetch_row();
    $avgmarkq = $mysqli->query("select avg(value) from marks where mid='$media[0]'");
    $avgmark =  $avgmarkq->fetch_row();
    echo '<form method=post style="margin:50px">';
    if (!$mark) {
        for ($i = 1; $i < 6; $i++) {
            echo '<input type="submit" class="star" formaction="media.php?mid=' . ($media[0])  . '&mark=' . $i . '" value="☆"></input>';
        }
    }
    if ($mark) {
        for ($i = 1; $i < 6; $i++) {
            if ($mark[0] == $i) echo '<input type="submit" style="background-color: rgb(64, 172, 205);"class="star" formaction="media.php?mid=' . ($media[0]) . '&mark=' . $i . '" value="☆"></input>';
            if ($mark[0] != $i) echo '<input type="submit" class="star" formaction="media.php?mid=' . ($media[0]) .  '&mark=' . $i . '" value="☆"></input>';
        }
    }
    echo '<div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;font-size: 30px;"> Средняя оценка:' . round($avgmark[0], 1) . '</div>';
    echo '</form>';
    $marksnames = array("Нет такова", "Ужасно", "Плохо", "Нормально", "Хорошо", "Превосходно");
    $typemarksq = $mysqli->query("select value, count(value) from marks where mid='$media[0]' group by value order by value ");
    if ($typemarksq->num_rows  > 0) {
        $arr = array();
        while ($typesmark =  $typemarksq->fetch_row()) {
            $str = '["' . $marksnames[$typesmark[0]] . '",' . $typesmark[1] . '],';
            array_push($arr, $str);
        }
        $str = implode("", $arr);
    ?>
        <script type="text/javascript">
            // Load the Visualization API and the corechart package.
            google.charts.load('current', {
                'packages': ['corechart']
            });
            // Set a callback to run when the Google Visualization API is loaded.
            google.charts.setOnLoadCallback(drawChart);
            // Callback that creates and populates a data table,
            // instantiates the pie chart, passes in the data and
            // draws it.
            function drawChart() {
                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Type TestDrive');
                data.addColumn('number', 'Percent hours');
                <?php echo "data.addRows([$str]);" ?>
                // Set chart options
                var options = {
                    'title': 'Информация об оценках'
                };
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        </script>
        <div id='chart_div' style='width: 600; height: 500px; margin: 0 10%;'></div><br>

        <script type="text/javascript">
            function clickRadio(el) {
                var siblings = document.querySelectorAll("input[type='radio'][name='" + el.name + "']");
                for (var i = 0; i < siblings.length; i++) {
                    if (siblings[i] != el)
                        siblings[i].oldChecked = false;
                }
                if (el.oldChecked)
                    el.checked = false;
                el.oldChecked = el.checked;
            }
        </script>
    <?php    }
    if (is_dir('resource/' . $mid)) {
        $dir = scandir('resource/' . $mid);
        $ii = 1;
        $str = "";
        $tabs = "";
        $tabslab = "";
        $tabscontent = "";
        $tabschecked = "";
        $tabschecked2 = "";
        for ($i = 2; $i < count($dir); $i++) {
            $tabs .= '<input id="tab_' . $ii . '" type="radio" name="tab"  onclick="clickRadio(this)"/>';
            $tabslab .= '<label for="tab_' . $ii . '" id="tab_l' . $ii . '">Серия ' . $ii . '</label>';
            $tabscontent .= '<div id="tab_c' . $ii . '"><video width="100%" height="auto" controls="controls"><source src="resource/' . $mid . '/' . iconv("windows-1251", "UTF-8", $dir[$i])  . '"></video><br></div>';
            $tabschecked .= '#tab_' . $ii . ':checked  ~ #tab_l' . $ii . '';
            if ($i < count($dir) - 1) $tabschecked .= ',';
            $tabschecked2 .= '#tab_' . $ii . ':checked ~ .tabs_cont #tab_c' . $ii . '';
            if ($i < count($dir) - 1) $tabschecked2 .= ',';
            $ii++;
        }

    ?>
        <style>
            .tabs {
                display: inline-block;
                position: static;
                margin: 0 10%;
                width: 80%;
                height: auto
            }

            .tabs label {
                color: #555;
                cursor: pointer;
                display: block;
                float: left;
                width: 150px;
                height: 45px;
                line-height: 45px;
                position: relative;
                top: 2px;
                text-align: center;
                background-color: rgb(16, 97, 151);
                border-radius: 16px;
                -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
                -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
                color: #fff;
            }

            .tabs input {
                position: absolute;
                left: -9999px;
            }

            <?php echo $tabschecked; ?> {
                background-color: rgb(64, 172, 205);
                border-color: rgb(64, 172, 205);
                top: 0;
                z-index: 3;
            }

            .tabs_cont {
                background-color: rgb(16, 97, 151);
                position: relative;
                z-index: 2;
                height: auto;
            }

            .tabs_cont>div {
                background-color: rgb(16, 97, 151);
                position: absolute;
                left: -9999px;
                top: 0;
                opacity: 0;
                -moz-transition: opacity .5s ease-in-out;
                -webkit-transition: opacity .5s ease-in-out;
                transition: opacity .5s ease-in-out;
            }

            <?php echo $tabschecked2; ?> {
                position: static;
                left: 0;
                opacity: 1;
            }
        </style>
        <section class="tabs">
            <?php echo $tabs . $tabslab; ?>
            <div style="clear:both"></div>

            <div class="tabs_cont">
                <?php echo $tabscontent; ?>
            </div>
        </section>
    <?php
    } else if (is_file('resource/' . $mid . '.mp4')) {
        echo '<video width="80%" height="auto" style="margin-left:10%" controls="controls"><source src="resource/' . $mid . '.mp4"></video>';
    } else if (is_file('resource/' . $mid . '.pdf')) {
        echo '<embed src="resource/' . $mid . '.pdf" width="80%" height="800" style="margin-left:10%"/>';
    } else echo '<div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;font-size: 30px;"> Ресурсы не обнаружены </div>';

    //комментарии
    if (isset($_GET['write'])) {
        if (($_GET['write'] == 1) && (strlen($_POST['text']) > 0)) {
            $text = $_POST['text'];
            $mysqli->query("insert into comments value (NULL,'$u[0]', '$media[0]', now(), '$text',NOW())");
            echo $mysqli->error;
        }
        if ($_GET['write'] == 2) {
            $cid = $_GET['comm'];
            $mysqli->query("delete from comments where id = '$cid'");
        }
    }
    $comments = $mysqli->query("select * from comments where mid='$mid'");
    if ($comments->num_rows  > 0) {
        echo '<div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;    font-size: 30px; margin: 50px">';
        echo 'Комментарии:';
        while ($comment =  $comments->fetch_row()) {
            $user2 = $mysqli->query("select * from users where id='$comment[1]'");
            $u2 =  $user2->fetch_row();
            echo '<div display:inline-box position:absolute style="font-family: Arial, Helvetica, sans-serif;font-size: 30px; margin: 50px">';
            echo '<table class="tab" style="float:left">';
            echo '<tr>';
            $roleq = $mysqli->query("select role from accounts where id = '$u2[4]'");
            $role =  $roleq->fetch_row();
            echo '<td style="width: auto">"' . $role[0] . '"</td>';
            echo '<td style="width: auto">' . $comment[3] . '</td>';
            echo '<td style="width: auto">' . $u2[1] . ':</td>';
            if (($u2[0] == $u[0]) || ($u[4] > 900)) {
                echo '<td><a style="width: auto" class="but" href="media.php?mid=' . ($media[0]) . '&write=2&comm=' . $comment[0] . '" >✖</a></td>';
            }
            echo '</tr>';
            echo '<tr style="background-color: rgb(245, 226, 173);color: #000;"><td>' . $comment[4] . '</td></tr>';
            echo '</table></div><br><br>';
        }
        echo '</div>';
    }
    ?>

    <form method=post style="vertical-align: middle; position: relative; ">
        <table class="tab">
            <tr>
                <td><br>Оставьте комментарий</td>
            </tr>
            <tr>
                <td><input class="txt" type="text" name="text" size="100%" maxlength="200" /></td>
            </tr>
            <tr>
                <td><input type="submit" style="width: 50%" class="but" formaction="media.php?<?php echo 'mid=' . ($media[0]) . '&write=1'  ?>" value="Отправить" /></td>
            </tr>
        </table>
    </form>
</body>

</html>
<?php ob_end_flush(); ?>