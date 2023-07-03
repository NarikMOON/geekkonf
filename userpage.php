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
    <title>Личный кабинет</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    <a href="#openModal" style="text-decoration:none">
        <div class="header">Geek Konf</div>
    </a>
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
            echo '<a style="width: 9%" href="management_panel.php" class="but">Управление</a>';
        }
        ?>
        <div id="openModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Связь с нами</h3>
                        <a href="#close" title="Close" class="close">×</a>
                    </div>
                    <div class="modal-body">
                        <div>Отправьте сообщение администратору, он будет рад(нет)</div>
                        <div>Так же можно использовать: geekkonf@mail.ru</div>
                        <form method=post>
                            <div>Укажите почту, если Вам требуется ответ и она не указана в профиле:</div>
                            <input type="text" class='txt' name="prioritymail" />
                            <div>Какая у Вас проблема?</div>
                            <textarea class="txt" name="message_text" cols="60" rows="5"></textarea>
                            <input type="submit" class="but" formaction="userpage.php?sendmessage=100" value="Отправить сообщение администратору">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
        <a style="width: 15%;" href="recommendation.php" class="but">Рекомендации</a>
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
        if (isset($_GET['changeaddress'])) {
            $newadress = $_POST['newadress'];
            $mysqli->query("delete from mails where uid = $u[0]");
            $mysqli->query("insert into mails value (NULL,$u[0], '$newadress',NOW());");
            if (!$mysqli->error) echo "Почта добавлена";
        }
        if (isset($_GET['sendmessage'])) {
            $theme = "Пишет $role[0] $u[1] : $u[0]";

            $text = $_POST['message_text'] . "\n Указанная почта для обращения: " . $_POST['prioritymail'];
            $addressq = $mysqli->query("select address from mails where uid = $u[0]");
            $address =  $addressq->fetch_row();
            mail('geekkonf@mail.ru', $theme, $text);
            if (isset($_POST['prioritymail'])) {
                $prioritymail = $_POST['prioritymail'];
                mail($prioritymail, "GEEKKONF", "Вы обратились к администрации с сообщением: " . $text);
                if (strlen($prioritymail) > 0) $mysqli->query("insert into mail_info value(NULL,$u[0],NOW(),'Вы обратились к администрации с сообщением: $text',NOW())");
            }
            if (strlen($address[0]) > 0) {

                mail($address[0], "GEEKKONF", "Вы обратились к администрации с сообщением: " . $text . "\n Указанная почта: $address[0]");

                if (strlen($address[0]) > 0) {
                    $mysqli->query("insert into mail_info value(NULL,$u[0],NOW(),'Вы обратились к администрации с сообщением: $text\n Указанная почта: $address[0]',NOW())");
                }
            }
            $result = $mysqli->query("select mails.address from mails, users where mails.uid=users.id and users.account>100;");
            while ($row =  $result->fetch_row()) {
                mail($row[0], "Управление", $theme . "\n Указанная почта: $address[0]");
            }
        }
        $addressq = $mysqli->query("select address from mails where uid = $u[0]");
        $address =  $addressq->fetch_row();
        ?>
        <h4>Ваша почта:</h4>
        <form method=post>
            <input type="text" name="newadress" class="txt" value="<?php echo $address[0]; ?>" />
            <input type="submit" class="but" formaction="userpage.php?changeaddress=1001" value="Изменить">
        </form>
        <h1>Оформление подписок</h1>
        <form method=post>
            <?php
            if (isset($_GET['restor'])) {
                if ($_GET['restor'] == "1331") {
                    $mysqli->query("delete from mail_options where uid = $u[0]");
                    $newopt = $_POST['themes'];
                    $in = "(";
                    for ($i = 0; $i < count($newopt); $i++) {
                        $in .= $newopt[$i] . ",";
                    }
                    $in .= "0)";
                    if ($in != "(0)") $mysqli->query("insert into mail_options (uid,tsid,log) select '$u[0]', theme_sends.id, NOW() from theme_sends where theme_sends.id in $in");
                }
            }



            $optionsq = $mysqli->query("select tsid from mail_options where uid=$u[0]");

            while ($options =  $optionsq->fetch_row()) {
                $arrayopt[] = $options[0];
            }
            //var_dump($arrayopt[0]);


            $themesq = $mysqli->query("select id, them from theme_sends");

            echo '<table class="tab" style="vertical-align:top">';
            while ($themes =  $themesq->fetch_row()) {
                echo '<tr><td>';
                echo '<div><input type="checkbox" name="themes[]" value="' . $themes[0] . (isset($arrayopt) && in_array($themes[0], $arrayopt) ? '" checked>' : '">');
                echo '<label for=' . $themes[0] . '>' . $themes[1] . '</label>';
                echo '</div></td></tr>'; //var_dump($themes[0]);
            }
            echo '</table><br><br>';

            ?>
            <input type="submit" class="but" formaction="userpage.php?restor=1331" value="Изменить">
        </form>
        <?php
        define('tFPDF_FONTPATH', "fpdf/font/");
        require_once("fpdf/tfpdf.php");
        $pdf = new TFPDF('P', 'pt', 'Letter');
        $pdf->SetAuthor("$u[1]");
        $pdf->AddPage();
        $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $pdf->SetFont('DejaVu', '', 14);
        $pdf->Ln(32);
        $pdf->SetDisplayMode('real', 'default');
        $pdf->SetFontSize(20);

        $pdf->MultiCell(
                $w = 0,
                $h = 0,
                $txt = "Отчет активности пользoвателя $u[1]",
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
                $txt = "Таблица оценок",
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
            );


        $pdf->SetFillColor(150, 255, 150);

        echo '<h3>Список вашей активности:</h3>';
        $table = '<table border="1" class="tab1"><th>Произведение</th><th>Тип</th><th>Оценка</th>';
        $tableq = $mysqli->query("select media.title, marks.value, types.title from media,marks,types where marks.mid=media.id and media.type=types.id and marks.uid = '$u[0]'");

        $pdf->SetXY(80, 150);
        $x0 = $pdf->GetX();

        $pdf->SetFontSize(14);

        $y0 = $pdf->GetY();
        //echo '<br>1 '.$y0.'<br>';
        $pdf->SetXY($x0, $y0);
        $pdf->MultiCell(
                $w = 230,
                $h = 50,
                $txt = "Произведение",
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
                $w = 200,
                $h = $y1 - $y0,
                $txt = "Тип произведения",
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
        $y2 = $pdf->GetY();
        //echo '<br>3 '.$y0.' '.$y2.'<br>';
        $pdf->SetXY($x0 + 430, $y0);
        $pdf->MultiCell(
                $w = 60,
                $h = $y2 - $y0,
                $txt = "Оценка",
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
        $y0 = $y2;
        if ($y0 > 600) {
            $pdf->AddPage();
            $y0 = $pdf->GetY();
        }
        $pdf->SetFontSize(12);
        $pdf->SetFillColor(220, 255, 220);
        $i = 0;
        $arr1 = array();
        $arr2 = array();

        while ($rowtab =  $tableq->fetch_row()) {
            $table .= '<tr><td>' . $rowtab[0] . '</td><td>' . $rowtab[2] . '</td><td>' . $rowtab[1] . '</td></tr>';
            $str = '"' . $rowtab[0] . '",';
            array_push($arr1, $str);
            $str = $rowtab[1] . ',';
            array_push($arr2, $str);

            $y0 = $pdf->GetY();
            //echo '<br>1 '.$y0.'<br>';
            $pdf->SetXY($x0, $y0);
            $pdf->MultiCell(
                    $w = 230,
                    $h = 50,
                    $txt = $rowtab[0],
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
                    $w = 200,
                    $h = $y1 - $y0,
                    $txt = $rowtab[2],
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
            $y2 = $pdf->GetY();
            //echo '<br>3 '.$y0.' '.$y2.'<br>';
            $pdf->SetXY($x0 + 430, $y0);
            $pdf->MultiCell(
                    $w = 60,
                    $h = $y2 - $y0,
                    $txt = $rowtab[1],
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
            $y0 = $y2;
            if ($y0 > 600) {
                $pdf->AddPage();
                $y0 = $pdf->GetY();
            }
            $i++;
        }
        $str = "['Произведение'," . implode("", $arr1) . "],";
        $str2 = "['Произведения'," . implode("", $arr2) . "],"; ?>

        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawVisualization);

            function drawVisualization() {
                // Some raw data (not necessarily accurate)
                var data = google.visualization.arrayToDataTable([
                    <?php echo $str . $str2 ?>
                ]);

                var options = {
                    title: 'Выставленные оценки',
                    vAxis: {
                        title: 'Оценка',
                        ticks: [0, 1, 2, 3, 4, 5]
                    },
                    seriesType: 'bars'
                };

                var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        </script>

        <?
        echo "<div id='chart_div' style='width: 1500; height: 500px;'></div>";
        $tableq = $mysqli->query("select avg(value) from marks where uid = '$u[0]'");
        $rowtab =  $tableq->fetch_row();
        $table .= '<tr><td>Всего оценок: ' . $i . '</td><td>Средняя оценка:</td><td>' . $rowtab[0] . '</td></tr>';
        $table .= '</table>';
        echo $table;
        $pdf->SetFillColor(150, 255, 150);
        $y0 = $pdf->GetY();
        //echo '<br>1 '.$y0.'<br>';
        $pdf->SetXY($x0, $y0);
        $pdf->MultiCell(
                $w = 230,
                $h = 50,
                $txt = "Всего оценок: " . $i,
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
                $w = 200,
                $h = $y1 - $y0,
                $txt = "Средняя оценка:",
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
        $y2 = $pdf->GetY();
        //echo '<br>3 '.$y0.' '.$y2.'<br>';
        $pdf->SetXY($x0 + 430, $y0);
        $pdf->MultiCell(
                $w = 60,
                $h = $y2 - $y0,
                $txt = $rowtab[0],
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
        $y0 = $y2;
        if ($y0 > 600) {
            $pdf->AddPage();
            $y0 = $pdf->GetY();
        }
        $pdf->Output("otchets/otchet_client_$u[1].pdf", "F");
        echo '<a class="but" href="otchets/otchet_client_' . $u[1] . '.pdf">Скачать отчёт</a>';
        ?>
</body>
</html>
<?php ob_end_flush(); ?>