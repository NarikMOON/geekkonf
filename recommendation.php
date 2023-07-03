<?php
session_start();
ob_start();
//error_reporting(1);
//ini_set('display_errors', 1);
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
    <title>Рекомендации</title>
</head>

<body <?php if (isset($_SESSION['theme'])) echo $_SESSION['theme']; ?>>
    
        <div class="header">Geek Konf</div>
    
    <div display:inline-box>
        <a style="width: 9%" href="authorization.php" class="but">Авторизация</a>
        <?php
        if (isset($_POST['login']) > 0) {
            $_SESSION['login'] = $_POST['login'];
            $_SESSION['password'] = $_POST['password'];
        }
        if (isset($_SESSION['login']))$login = $_SESSION['login'];
        if (isset($_SESSION['password']))$password = $_SESSION['password'];
        if (!(isset($login)&&isset($password))) {
            echo '<a  href="authorization.php" class="but">Вы ввели не все необходимые сведения!</a>';
            exit;
        }
        $mysqli = new mysqli('localhost', 'root', 'mysql', 'GeekKonfClubDatabase', '3306');
        $user = $mysqli->query("select * from users where login ='$login' and password = sha1('$password')");
        $u = $user->fetch_row();
        if (!isset($u[0]) ) {
            echo '<a  href="authorization.php" class="but">Логин и пароль не верны!</a>';
            exit;
        }
        if ($u[4] >= 900) {
            echo '<a style="width: 9%" href="management_panel.php" class="but">Управление</a>';
        }
        ?>
        <a style="width: 9%" href="messenger.php" class="but">Чат</a>


        <a style="width: 9%" href="proposal.php" class="but">Предложение</a>
        <a style="width: 15%; background-color:rgb(64, 172, 205)" href="#" class="but">Рекомендации</a>
        <a class='btn-toggle' href="?theme=<?php  echo ((isset($_SESSION['theme']))&&($_SESSION['theme'] == 'class="dark"')) ? 'light' : 'dark'; ?>">DT</a>
        <a href="userpage.php">
            <table class="tab" style="float:right">
                <tr>
                    <?php
                    $roleq = $mysqli->query("select role from accounts where id = '$u[4]'");
                    $role = $roleq->fetch_row(); ?>
                    <td style="width: auto">"<?php echo $role[0] ?>"</td>
                    <td style="width: auto"><?php echo $u[1] ?>:</td>
                    <td style="width: auto"><?php echo $u[0] ?></td>
                    <td style="width: auto">
                </tr>
            </table>
        </a>
        

    </div>
    <div display:inline-box>
        <form method=post>
            <input hidden name='check' value="1" />
            <input class="txt" type="text" name="part_of_title" size="105" maxlength="100" value=<?php if (isset($_POST['part_of_title'])) echo '"' . $_POST['part_of_title'] . '"' ?> ></input>
            <br>
            <div class="dropdown">
                <a href='#' class="but">Фильтр типов</a>
                <div class="dropdown-content">
                    <?php
                    if ($mysqli->errno) {
                        echo 'Error 37373737';
                        exit;
                    }
                    $alltypes = $mysqli->query("select * from types");
                    echo '<fieldset class="filter"name="types">';
                    echo '<legend>Выберите типы для поиска:</legend>';
                    while ($at = $alltypes->fetch_row()) {
                        echo '<div><input type="checkbox" name="alltypes[]" value="' . $at[0] . (((isset($_POST['check']) && in_array($at[0], $_POST['alltypes'])) or (!isset($_POST['check']))) ? '" checked>' : '">');
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
                        while ($ag = $allgenres->fetch_row()) {
                            echo '<div ><input type="checkbox" name="allgenres[]" value="' . $ag[0] . (((isset($_POST['check']) && in_array($ag[0], $_POST['allgenres'])) or (!isset($_POST['check']))) ? '" checked>' : '">');
                            echo '<label for=' . $ag[0] . '>' . $ag[1] . '</label>';
                            echo '</div>';
                        } ?>
                        <input hiden style="display:none" type="checkbox" name="allgenres[]" value="0" checked>
                    </fieldset>
                </div>
            </div> <!--закрытие тега скрывающего-->
            <input type="date" class="txt" name="sdate" value="<?php echo $_POST['sdate'] ?>"></input>-<input type="date" class="txt" name="edate" value="<?php echo $_POST['edate'] ?>"></input>
            <input type="submit" style="width: 15%" class="but" formaction="recommendation.php" value="Поиск"></input>
            <input type="submit" class="but" formaction="recommendation.php?rec=99" value="Подбор рекомендаций"></input>
        </form>
    </div>
    <br>
    <?php
    if (isset( $_POST['part_of_title'])) $part_of_title=$_POST['part_of_title']; else $part_of_title = '';
    $query = "select * from media where id not in (select mid from recommends where checker != 2) and title like '" . $part_of_title . "%'";
    if (isset($_POST['alltypes']))$alltypes = $_POST['alltypes'];else $alltypes =array();
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
    if (isset($_POST['allgenres']))$allgenres = $_POST['allgenres'];else $allgenres =array();
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
    if (isset($_POST['sdate']))$sdate = $_POST['sdate'];
    if (!empty($sdate)) $query = $query . " and year > '" . $sdate . "'";
    if (isset($_POST['edate']))$edate = $_POST['edate'];
    if (!empty($edate)) $query = $query . " and year < '" . $edate . "'";
    if (isset($_GET['rec']) && $_GET['rec'] == 99) {
        $ret_code;
        exec("python recommendation.py $u[0]", $output, $ret_code);
       
        $query = $query . " and id in (";
        for ($i = 0; $i < count($output); $i++) {
            $query = $query . $output[$i];
            if ($i < count($output) - 1) $query = $query . ', ';
        }
        $query = $query . ")"; // and not in (select mid from marks where uid = 'u[0]')
    }
    //echo $query;
    $result = $mysqli->query($query);
    echo '<div display:inline-box position:absolute>';
    define('tFPDF_FONTPATH', "fpdf/font/"); /////////////////////////////////////////////////////////////////////////////////////
    require_once("fpdf/tfpdf.php");
    $pdf = new TFPDF('P', 'pt', 'Letter');
    $pdf->SetAuthor("$u[1]");
    $pdf->AddPage();
    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
    $pdf->SetFont('DejaVu', '', 14);
    $pdf->Ln(32);
    $pdf->SetDisplayMode('real', 'default');
    $pdf->SetFontSize(20);
    $time = date('y-m-d_h:i:s');
    $pdf->MultiCell(
        $w = 0,
        $h = 30,
        $txt = "Список запрошенных произведений $u[1] от " . $time,
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
    $pdf->SetFillColor(150, 255, 150);
    $pdf->SetXY(80, 150);
    $x0 = $pdf->GetX();
    $pdf->SetFontSize(14);
    $y0 = $pdf->GetY();
    //echo '<br>1 '.$y0.'<br>';
    $pdf->SetXY($x0, $y0);
    $pdf->MultiCell(
        $w = 230,
        $h = 100,
        $txt = "Произведение",
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
        $maxh = 0,
        $valign = 'M',
        $fitcell = true
    );
    //$x1 = $pdf->GetX();
    $y1 = $pdf->GetY();
    //echo '<br>2 '.$y1.'<br>';
    $pdf->SetXY($x0 + 230, $y0);
    $pdf->MultiCell(
        $w = 120,
        $h = 50,
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
        $maxh = 0,
        $valign = 'M',
        $fitcell = true
    );
    //$x2 = $pdf->GetX();
    $y2 = $pdf->GetY();
    //echo '<br>3 '.$y0.' '.$y2.'<br>';
    $pdf->SetXY($x0 + 350, $y0);
    $pdf->MultiCell(
        $w = 70,
        $h = $y2 - $y0,
        $txt = "Рейтинг",
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
        $maxh = 0,
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
    while ($row = $result->fetch_row()) {
        $url = 'media.php?mid=' . ($row[0]);
        echo '<a class="media" href=' . $url . ' ><strong>';
        echo '<input hidden name="check" value="1"/>';
        echo '<img class="media" alt="Картинки нет, но вы держитесь" src="./pic/' . $row[0] . '.jpg"/>';
        echo stripslashes($row[2]);
        $type = $mysqli->query("select title from types where id=" . ($row[1]));
        $tr = $type->fetch_row();
        echo '</strong><br />';
        echo stripslashes($tr[0]);
        echo '</strong><br />Жанры:';
        $genres = $mysqli->query("select genres.name from relationships_genres , genres  where genres.id=relationships_genres.gid and relationships_genres.uid=" . ($row[0]));
        while ($gr = $genres->fetch_row()) {
            echo ' ' . $gr[0];
        }
        echo '<br />Дата выхода: ';
        echo stripslashes($row[3]);
        echo '<br/>Страна: ' . $row[5];
        echo '</p>';
        echo '</a>';
        $i = $i + 1;
        $newq = $mysqli->query("select media.title, avg(marks.value), types.title from media,marks,types where marks.mid=media.id and media.type=types.id and media.id = '$row[0]'");
        //echo  $mysqli->errno;
        $rowtab = $newq->fetch_row();
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
            $w = 120,
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
        $pdf->SetXY($x0 + 350, $y0);
        $pdf->MultiCell(
            $w = 70,
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
    }
    echo '</div><p>Найдено произведений: ' . $i . '</p>';
    $y0 = $pdf->GetY();
    $pdf->SetFillColor(150, 255, 150);
    //echo '<br>1 '.$y0.'<br>';
    $pdf->SetXY($x0, $y0);
    $pdf->MultiCell(
        $w = 230,
        $h = 50,
        $txt = "Найдено произведений:",
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
        $txt =  $i,
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
    $pdf->SetXY($x0 + 350, $y0);
    $pdf->MultiCell(
        $w = 70,
        $h = $y2 - $y0,
        $txt = '',
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
    $pdf->Output("otchets/list_$u[1].pdf", "F");
    echo '<a class="but" href="otchets/list_' . $u[1] . '.pdf">Скачать отчёт</a>';
    $mysqli->close();
    ?>
</body>

</html>
<?php ob_end_flush(); ?>