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
    <title>Управление рассылкой</title>
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
                    $role = $roleq->fetch_row(); ?>
                    <td style="width: auto">"<? echo $role[0] ?>"</td>
                    <td style="width: auto"><? echo $u[1] ?>:</td>
                    <td style="width: auto"><? echo $u[0] ?></td>
                </tr>
            </table>
        </a>
        <br><br>
        <?php
        if (isset($_GET['add'])) {
            if ($_GET['add'] == "100") {
                $tytle = $_POST['part_of_title'];
                $mysqli->query("insert into theme_sends values (NULL, '$tytle',NOW())");
                //echo $_GET['add']."/////////////////////////////////////////////////////////////////////".$tytle. mysql_error();
            }
            if ($_GET['add'] == 200) {
                $themid = $_GET['selectedthem'];
                //var_dump($themid);
                $mysqli->query("delete from theme_sends where id=$themid");
                $mysqli->query("delete from mail_options where tsid=$themid");
                $mysqli->query("delete from mail_sends where them=$themid");
                echo  $mysqli->error;
            }
        } ?>
        <form method=post><br>
            <table class="tab" style="vertical-align:top">
                <?
                $usersq = $mysqli->query("select users.id, users.name, mails.address from mails, users where users.id=mails.uid");
                while ($users = $usersq->fetch_row()) { ?>
                    <tr>
                        <td>
                            <div><input type="checkbox" name="selectedusers[]" value="<? echo $users[2] ?>" checked>
                                <label for=<? echo $users[0] ?>>#<? echo $users[0] ?> <? echo $users[1] ?></label>
                                </div>
                        </td>
                        <td><input type="submit" class="but" formaction="mailjob.php?sender=1200&selectedmail=<? echo $users[2] ?>" value="Отправить индивидуально"></td>
                    </tr>
                <?
                } ?>
            </table><br><br><input type="submit" class="but" formaction="mailjob.php?sender=1250" value="Отправить всем">
            <input type="submit" class="but" formaction="mailjob.php?sender=1290" value="Отправить отмеченным">
            <br><br><textarea class="txt" name="message_text" cols="60" rows="5"></textarea><br><br>
            <table class="tab" style="vertical-align:top">
                <?
                $themesq = $mysqli->query("select id, them from theme_sends");
                while ($them = $themesq->fetch_row()) {
                    echo '<tr><td>';
                    echo '<div><input type="radio" name="them[]" value="' . $them[0] . '">';
                    echo '<label for=' . $them[0] . '>' . $them[1] . '</label></div></td>';
                    echo '<td><input type="submit" class="but" formaction="mailjob.php?add=200&selectedthem=' . $them[0] . '" value="X"></td>';
                    echo '</tr>';
                } ?>
            </table><br><br>
            <input type="submit" class="but" formaction="mailjob.php?sender=2100" value="Отправить рассылку по теме">
        </form>
        <form method=post><br>
            <input class="txt" type="text" name="part_of_title" size="105" maxlength="100" />
            <input type="submit" class="but" formaction="mailjob.php?add=100" value="Добавить новую тему рассылки">
        </form>
        <?
        if (isset($_GET['sender'])) {
            //var_dump($_GET);var_dump($_POST);
            $message = $_POST['message_text'];

            if ($_GET['sender'] == "1200") {
                $selectedmail = $_GET['selectedmail'];
                mail($selectedmail, "GEEKKONF: Индивидуальное сообщение", $message);
                $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$selectedmail'),NOW(),'$message',NOW())");

                //echo '1200   '.$_GET['sender'].$selectedmail.'   ';
                mail('geekkonf@mail.ru', "GEEKKONF: Индивидуальное сообщение", $message . "\n Отправлено: " . $selectedmail);
            }
            if ($_GET['sender'] == "1250") {
                $selectedusers = $_POST['selectedusers'];
                $sended = "\n Отправлено: ";
                $allmailsq = $mysqli->query("select mails.address from mails");
                while ($mails = $allmailsq->fetch_row()) {
                    mail($mails[0], "GEEKKONF: Общая рассылка", $message);
                    $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$mails[0]'),NOW(),'$message',NOW())");

                    //echo '1250   '.$_GET['sender'].$mails[0].'  ';
                    $sended .= $mails[0] . " ";
                }
                mail('geekkonf@mail.ru', "GEEKKONF: Общая рассылка", $message . $sended);
            }
            if ($_GET['sender'] == "1290") {
                $selectedusers = $_POST['selectedusers'];
                $sended = "\n Отправлено: ";
                for ($i = 0; $i < count($selectedusers); $i++) {
                    $selectedusers[$i];
                    mail($selectedusers[$i], "GEEKKONF: Выборочная рассылка", $message);
                    $mysqli->query("insert into mail_info value(NULL,(select uid from mails where address='$selectedusers[$i]'),NOW(),'$message',NOW())");
                    //echo '1290   '.$_GET['sender'].$selectedusers[$i].'   ';
                    $sended .= $selectedusers[$i] . " ";
                }
                mail('geekkonf@mail.ru', "GEEKKONF: Выборочная рассылка", $message . $sended);
            }
            if ($_GET['sender'] == "2100") {
                $selectedusers = $_POST['selectedusers'];
                $sended = "\n Отправлено: ";
                $themid = $_POST['them'][0];
                $themq = $mysqli->query("select them from theme_sends where id = $themid");
                $theme = $themq->fetch_row();

                $allmailsq = $mysqli->query("select mails.address from mails, mail_options where mails.uid=mail_options.uid and mail_options.tsid=$themid");
                $mysqli->query("insert into mail_sends value(NULL,NOW(),'$themid','$message',NOW())");
                $sidq = $mysqli->query("select max(id) from mail_sends");
                $sid = $sidq->fetch_row();
                //echo '2100 '. var_dump($them[0]);
                while ($mails = $allmailsq->fetch_row()) {
                    mail($mails[0], $theme[0], $message);
                    $mysqli->query("insert into sends_list value(NULL,(select uid from mails where address='$mails[0]'),$sid[0],NOW())");

                    //echo '2100   '.$_GET['sender'].$mails[0].'  ';
                    $sended .= $mails[0] . " ";
                }
                mail('geekkonf@mail.ru', $theme[0], $message . $sended);
            }
        }

        ?>
        <h1>Статистика рассылок</h1>
        <?php
        $themesq = $mysqli->query("select id, them from theme_sends");
        while ($theme = $themesq->fetch_row()) {
            echo '<h4>#' . $theme[0] . " " . $theme[1] . '</h4><br>';
            $i = 0;
            $statdateq = $mysqli->query("select id,date from mail_sends where them = $theme[0]");
            echo $mysqli->error;
            while ($state = $statdateq->fetch_row()) {
                //var_dump($state);
                $countq = $mysqli->query("select count(id) from sends_list where sid=$state[0]");
                $count = $countq->fetch_row();
                echo $state[1] . ' Отправлено ' . $count[0] . ' сообщений<br>';
                $i += $count[0];
            }
            echo 'Всего за все время: ' . $i;
        }

        define('tFPDF_FONTPATH', "fpdf/font/"); /////////////////////////////////
        require_once("fpdf/tfpdf.php");
        $pdf = new TFPDF('P', 'pt', 'Letter');
        $pdf->SetAuthor("$u[1]");
        $pdf->AddPage();
        $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $pdf->SetFont('DejaVu', '', 14);
        $pdf->Ln(32);
        $pdf->SetDisplayMode('real', 'default');
        $pdf->SetFontSize(20);

        $time = date('ymd_his');
        $pdf->MultiCell(
            $w = 0,
            $h = 30,
            $txt = "Сводка отправки сообщений в период" . ((isset($_POST['sdate'])) ? " с " . $_POST['sdate'] : '') . ((isset($_POST['edate'])) ? " до " . $_POST['edate'] : ''),
            $border = 0,
            $align = 'C',
            $fill = 0,
            $ln = "1100",
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $valign = 'M',
            $fitcell = true
        );
        $pdf->Ln(25);
        $pdf->MultiCell(
            $w = 0,
            $h = 0,
            $txt = "Таблица рассылок",
            $border = 0,
            $align = 'C',
            $fill = 0,
            $ln = 1,
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $valign = 'M',
            $fitcell = true
        ); ?>

        <form method=post>
            <input type="date" class="txt" name="sdate" value=<? if (isset($_POST['sdate'])) echo $_POST['sdate'];  ?>></input>-<input type="date" class="txt" name="edate" value=<? if (isset($_POST['edate'])) echo $_POST['edate'];  ?>></input>
            <input type="submit" style="width: 15%" class="but" formaction="mailjob.php" value="Определить границы"></input>
            <input hidden name='login' value=<?php echo '"' . $login . '"' ?> />
            <input hidden name='password' value=<?php echo '"' . $password . '"' ?> />
        </form>
        <?php
        $pdf->SetFillColor(150, 255, 150);
        $pdf->SetXY(80, 200);
        $x0 = $pdf->GetX();

        $pdf->SetFontSize(14);
        $y0 = $pdf->GetY();
        //echo '<br>1 '.$y0.'<br>';
        $pdf->SetXY($x0, $y0);
        $pdf->MultiCell(
            $w = 230,
            $h = 50,
            $txt = "Дата",
            $border = 1,
            $align = 'J',
            $fill = 1,
            $ln = 0,
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 3 * $h,
            $valign = 'M',
            $fitcell = true
        );
        //$x1 = $pdf->GetX();
        $y1 = $pdf->GetY();
        //echo '<br>2 '.$y1.'<br>';

        $pdf->SetXY($x0 + 230, $y0);
        $pdf->MultiCell(
            $w = 120,
            $h = $y1 - $y0,
            $txt = "Сообщений",
            $border = 1,
            $align = 'C',
            $fill = 1,
            $ln = 0,
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = $h,
            $valign = 'M',
            $fitcell = true
        );
        echo '<h3>Сводка по датам:</h3>'; //
        $query = "select month(mail_sends.date),year(mail_sends.date) from mail_sends where id>0";
        if (isset($_POST['sdate'])) $sdate = $_POST['sdate'];
        //var_dump($sdate);
        if (!empty($sdate)) $query = $query . " and date > '" . $sdate . "'";
        if (isset($_POST['edate'])) $edate = $_POST['edate'];
        if (!empty($edate)) $query = $query . " and date < '" . $edate . "'";
        $query .= " group by year(mail_sends.date),month(mail_sends.date)";
        $tableq = $mysqli->query($query);
        echo  $mysqli->error;
        $table = '<table border="1" class="tab1"><th>Дата</th><th>Сообщений</th>';
        $i = 0;
        $arr1 = array();
        $arr2 = array();
        while ($row = $tableq->fetch_row()) {
            $monthes = array("Нулябрь", "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
            $table .= "<tr><td>Месяц:</td><td>" . $monthes[$row[0]] . ' ' . $row[1] . "</td></tr>";
            $pdf->SetFillColor(220, 255, 220);
            $y0 = $pdf->GetY();
            //echo '<br>1 '.$y0.'<br>';
            $pdf->SetXY($x0, $y0);
            $pdf->MultiCell(
                $w = 230,
                $h = 50,
                $txt = "Месяц",
                $border = 1,
                $align = 'J',
                $fill = 1,
                $ln = 0,
                $x = '',
                $y = '',
                $reseth = true,
                $stretch = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = 3 * $h,
                $valign = 'M',
                $fitcell = true
            );
            //$x1 = $pdf->GetX();
            $y1 = $pdf->GetY();
            //echo '<br>2 '.$y1.'<br>';

            $pdf->SetXY($x0 + 230, $y0);
            $pdf->MultiCell(
                $w = 120,
                $h = $y1 - $y0,
                $txt = $monthes[$row[0]],
                $border = 1,
                $align = 'C',
                $fill = 1,
                $ln = 0,
                $x = '',
                $y = '',
                $reseth = true,
                $stretch = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = $h,
                $valign = 'M',
                $fitcell = true
            );
            //$x2 = $pdf->GetX();
            $i0 = 0;
            $q = $mysqli->query("select mail_sends.date, count(sends_list.id) from mail_sends, sends_list  where mail_sends.id = sends_list.sid and month(mail_sends.date) = '$row[0]' and year(mail_sends.date)= '$row[1]' group by mail_sends.date order by mail_sends.date");
            echo $mysqli->error;


            while ($rower = $q->fetch_row()) {
                $str = '["' . $rower[0] . '",' . $rower[1] . '],';
                //echo $str;
                array_push($arr1, $str);
                //echo $str.'<br>';
                $table .= "<tr><td>" . $rower[0] . "</td><td>" . $rower[1] . "</td></tr>";
                $pdf->SetFillColor(200, 190, 200);
                $y0 = $pdf->GetY();
                //echo '<br>1 '.$y0.'<br>';
                $pdf->SetXY($x0, $y0);
                $pdf->MultiCell(
                    $w = 230,
                    $h = 50,
                    $txt = $rower[0],
                    $border = 1,
                    $align = 'С',
                    $fill = 1,
                    $ln = 0,
                    $x = '',
                    $y = '',
                    $reseth = true,
                    $stretch = 0,
                    $ishtml = false,
                    $autopadding = true,
                    $maxh = 3 * $h,
                    $valign = 'M',
                    $fitcell = true
                );
                //$x1 = $pdf->GetX();
                $y1 = $pdf->GetY();
                //echo '<br>2 '.$y1.'<br>';

                $pdf->SetXY($x0 + 230, $y0);
                $pdf->MultiCell(
                    $w = 120,
                    $h = $y1 - $y0,
                    $txt = $rower[1],
                    $border = 1,
                    $align = 'C',
                    $fill = 1,
                    $ln = 0,
                    $x = '',
                    $y = '',
                    $reseth = true,
                    $stretch = 0,
                    $ishtml = false,
                    $autopadding = true,
                    $maxh = $h,
                    $valign = 'M',
                    $fitcell = true
                );
                //$x2 = $pdf->GetX();
                $i0 += $rower[1];
                $y0 = $y1;
                if ($y0 > 600) {
                    $pdf->AddPage();
                    $y0 = $pdf->GetY();
                }
            }
            $y0 = $pdf->GetY();
            //echo '<br>1 '.$y0.'<br>';
            $pdf->SetXY($x0, $y0);
            $pdf->MultiCell(
                $w = 230,
                $h = 50,
                $txt = 'Всего за месяц:',
                $border = 1,
                $align = 'С',
                $fill = 1,
                $ln = 0,
                $x = '',
                $y = '',
                $reseth = true,
                $stretch = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = 3 * $h,
                $valign = 'M',
                $fitcell = true
            );
            //$x1 = $pdf->GetX();
            $y1 = $pdf->GetY();
            //echo '<br>2 '.$y1.'<br>';

            $pdf->SetXY($x0 + 230, $y0);
            $pdf->MultiCell(
                $w = 120,
                $h = $y1 - $y0,
                $txt = $i0,
                $border = 1,
                $align = 'C',
                $fill = 1,
                $ln = 0,
                $x = '',
                $y = '',
                $reseth = true,
                $stretch = 0,
                $ishtml = false,
                $autopadding = true,
                $maxh = $h,
                $valign = 'M',
                $fitcell = true
            );
            $table .= "<tr><td>Всего за месяц:</td><td>" . $i0 . "</td></tr>";
            $arr2[] = $i0;
            $i += $i0;
        }
        $str = implode("", $arr1); ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Месяц', 'Кол-во разосланных сообщений'],
                    <?php echo $str ?>
                ]);

                var options = {
                    title: 'График рассылок',
                    legend: {
                        position: 'bottom'
                    }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }
        </script>
        <div id="curve_chart" style="width: 900px; height: 500px"></div>

        <?php
        $table .= "<tr><td>Всего за период:</td><td>" . $i . "</td></tr></table>";
        $pdf->SetFillColor(150, 255, 150);
        $y0 = $pdf->GetY();
        //echo '<br>1 '.$y0.'<br>';
        $pdf->SetXY($x0, $y0);
        $pdf->MultiCell(
            $w = 230,
            $h = 50,
            $txt = 'Всего за период:',
            $border = 1,
            $align = 'С',
            $fill = 1,
            $ln = 0,
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = 3 * $h,
            $valign = 'M',
            $fitcell = true
        );
        //$x1 = $pdf->GetX();
        $y1 = $pdf->GetY();
        //echo '<br>2 '.$y1.'<br>';

        $pdf->SetXY($x0 + 230, $y0);
        $pdf->MultiCell(
            $w = 120,
            $h = $y1 - $y0,
            $txt = $i,
            $border = 1,
            $align = 'C',
            $fill = 1,
            $ln = 0,
            $x = '',
            $y = '',
            $reseth = true,
            $stretch = 0,
            $ishtml = false,
            $autopadding = true,
            $maxh = $h,
            $valign = 'M',
            $fitcell = true
        );

        $pdf->Output("otchets/otchet_mail.pdf", "F");
        echo '<br><a class="but" href="otchets/otchet_mail.pdf">Скачать отчёт</a><br>';
        echo $table;

        ?>
        <h1>Статистические показатели динамики рассылки</h1>
        <?php
        $arr3 = array();
        $arr4 = array();
        //var_dump($arr2);
        $table2 = '<table border="1" class="tab1">';
        $table2 .= '<th rowspan = 2 style="text-align: center;">Период</th>';
        $table2 .= '<th rowspan = 2 style="text-align: center;">Электронных писем</th>';
        $table2 .= '<th colspan = 2 style="text-align: center;">Абсолютный прирост</th>';
        $table2 .= '<th colspan=2 style="text-align: center;">Темп роста, %</th>';
        $table2 .= '<th colspan=2 style="text-align: center;">Темп прироста, %</th>';
        $table2 .= '<tr>';
        $table2 .= '<td style="text-align: center;">Цепной</td>';
        $table2 .= '<td style="text-align: center;">Базисный</td>';
        $table2 .= '<td style="text-align: center;">Цепной</td>';
        $table2 .= '<td style="text-align: center;">Базисный</td>';
        $table2 .= '<td style="text-align: center;">Цепной</td>';
        $table2 .= '<td style="text-align: center;">Базисный</td>';
        $table2 .= '</tr>';
        $table2 .= '<tr><td>' . (1) . '</td><td>' . ($arr2[0]) . '</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>';

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
            $table2 .= '<tr><td>' . ($j + 1) . '</td><td>' . ($arr2[$j]) . '</td><td>' . number_format($arr2[$j] - $arr2[$j - 1], 2, '.', ',') . '</td><td>' . number_format($arr2[$j] - $arr2[0], 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[$j - 1] * 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[0] * 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[$j - 1] * 100 - 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[0] * 100 - 100, 2, '.', ',') . '</td></tr> ';
            $str = '[' . ($j + 1) . ',' . number_format($arr2[$j] / $arr2[$j - 1] * 100 - 100, 2, '.', ',') . ',' . number_format($arr2[$j] / $arr2[0] * 100 - 100, 2, '.', ',') . '],';
            //echo $str;
            array_push($arr3, $str);
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
        $str = implode("", $arr3);

        $str1 = implode("", $arr4);
        //echo $str1;
        $table3 .= '</table>';
        //echo $str1;
        //echo $str;
        $table2 .= '</table>';
        echo $table2;

        echo '<h1>Задание 1</h1>';
        echo $table3;
        ?>
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Месяц', 'Цепной темп прироста', 'Базисный темп прироста'],
                    <?php echo $str ?>
                ]);
                var options = {
                    title: 'Динамика рассылок',
                    curveType: 'function',
                    legend: {
                        position: 'bottom'
                    },
                    trendlines: {
                        0: {},
                        1: {}
                    },
                };
                var chart = new google.visualization.LineChart(document.getElementById('curve_chart1'));

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
        <div id="curve_chart1" style="width: 900px; height: 500px"></div>
        <h4>По графику видно периодические скачки темпа прироста охвата рассылки каждые 2-3 месяца. Тренд цепного прироста возрастает, а тренд базисного прироста константа, и что хорошо - больше нуля.</h4>
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
            $table2 .= '<tr><td>' . ($j + 1) . '</td><td>' . ($arr2[$j]) . '</td><td>' . number_format($arr2[$j] - $arr2[$j - 1], 2, '.', ',') . '</td><td>' . number_format($arr2[$j] - $arr2[0], 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[$j - 1] * 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[0] * 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[$j - 1] * 100 - 100, 2, '.', ',') . '</td><td>' . number_format($arr2[$j] / $arr2[0] * 100 - 100, 2, '.', ',') . '</td></tr> ';
            $str = '[' . ($j + 1) . ',' . number_format($arr2[$j] / $arr2[$j - 1] * 100 - 100, 2, '.', ',') . ',' . number_format($arr2[$j] / $arr2[0] * 100 - 100, 2, '.', ',') . '],';
            //echo $str;
            array_push($arr3, $str);
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
        $str = implode("", $arr3);
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


        <?php
        echo '<br><h1>Лабораторная работа 8.1.1</h1><br>';
        require_once('fun.php');
        HipoTreng($arr2);

        echo '<h1>Лабораторная работа 8.2.1</h1><br>';
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


        <h1>ЛР9.1</h1><br><?php echo $var['lr9'] ?>




</body>

</html>
<?php ob_end_flush(); ?>