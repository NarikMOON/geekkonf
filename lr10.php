<?php
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
    <title>ЛР10</title>
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
    <br><br>
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <h1>Задание 1. Диаграмма областей</h1>
    <h2>Сглаживание временных рядов с помощью скользящих средних. Ряд динамики выхода произведений</h2>
    <?php
    $graf1q = $mysqli->query('select CONCAT("new Date(",YEAR(media.year),",0,1)"), count(media.id), types.title from types, media where media.type=types.id group by year(media.year) order by media.year');
    $arr1 = array();
    while ($graf1 =  $graf1q->fetch_row()) {
        array_push($arr1, $graf1[1]);
    }
    $arr2 = array();
    $str1 = '[' . (1) . ',' . $arr1[0] . ',';
    $str1 .= number_format((5 * $arr1[0] + 2 * $arr1[1] - $arr1[2]) / 6, 2, '.', ',') . ',';
    $str1 .= number_format((39 * $arr1[0] + 8 * $arr1[1] - 4 * $arr1[2] - 4 * $arr1[3] + 1 * $arr1[4] + 4 * $arr1[5] - 2 * $arr1[6]) / 42, 2, '.', ',') . ',';
    $str1 .= number_format((31 * $arr1[0] + 9 * $arr1[1] - 3 * $arr1[2] - 5 * $arr1[3] + 3 * $arr1[4]) / 35, 2, '.', ',') . '],';
    //echo count($arr1);
    array_push($arr2, $str1);
    for ($j = 1; $j < count($arr1); $j++) {
        $str1 = '[' . ($j + 1) . ',' . $arr1[$j] . ',';
        if (($j > 0) and ($j < count($arr1) - 1)) {

            $str1 .= number_format(($arr1[$j - 1] + $arr1[$j] + $arr1[$j + 1]) / 3, 2, '.', ',') . ',';
        } else {

            $str1 .= 'undefined,';
        }
        if (($j > 2) and ($j < count($arr1) - 3)) {

            $str1 .= number_format(($arr1[$j - 3] + $arr1[$j - 2] + $arr1[$j - 1] + $arr1[$j] + $arr1[$j + 1] + $arr1[$j + 2] + $arr1[$j + 3]) / 7, 2, '.', ',') . ',';
        } else {

            $str1 .= 'undefined,';
        }
        if (($j > 1) and ($j < count($arr1) - 2)) {

            $str1 .=            number_format((-3 * $arr1[$j - 2] + 12 * $arr1[$j - 1] + 17 * $arr1[$j] + 12 * $arr1[$j + 1] - 3 * $arr1[$j + 2]) / 35, 2, '.', ',') . '],';
        } else {

            $str1 .= 'undefined],';
        }
        array_push($arr2, $str1);
    }

    $str1 = implode("", $arr2);
    ?>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Год', 'Фактические значения', 'l3', 'l7', 'l5w'],
                <?php echo $str1 ?>
            ]);
            var options = {
                title: 'Востановление краевых значений и прогноз',
                curveType: 'function',
                legend: {
                    position: 'bottom'
                },
            };
            var chart = new google.visualization.AreaChart(document.getElementById('curve_chart1'));
            chart.draw(data, options);
        }
    </script>
    <div id="curve_chart1" style="width: 900px; height: 500px"></div>

    <h1>Задание 2. Пузырьковая диаграмма</h1>
    <h2>Соотношение оценок и произведений</h2>
    (ось X – произведение, ось Y – количество оценок, цвет – тип произведения, размер пузырька – средняя оценка)
    <?php
    $arr1 = array();
    $query = $mysqli->query("select media.title, count(marks.id), types.title, avg(marks.value) from media,marks,types where marks.mid=media.id and media.type=types.id group by media.id order by media.year");
    $i = 1;
    while ($row =  $query->fetch_row()) {
        //echo '<br>'.var_dump($row);
        $str = "['$row[0]',$i,$row[1],'$row[2]',$row[3]],";
        array_push($arr1, $str);
        $i++;
    }
    $str1 = implode("", $arr1);  //echo $str1;  
    ?>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Произведениe', 'ID', 'Число оценок', 'Тип', 'Средний рейтинг'],
                <?php echo $str1 ?>
            ]);
            var options = {
                title: 'Рейтинг произведений по типам',
                curveType: 'function',
                vAxis: {
                    format: '0'
                },
                hAxis: {
                    format: '0'
                },
                legend: {
                    position: 'bottom'
                },
                bubble: {
                    textStyle: {
                        fontSize: 5
                    }
                }
            };
            var chart = new google.visualization.BubbleChart(document.getElementById('curve_chart2'));
            chart.draw(data, options);
        }
    </script>
    <div id="curve_chart2" style="width: 1400px; height: 900px"></div>

    <h1>Задание 3. Календарь</h1>
    <h2>Активность рассылки</h2>
    <?php
    $arr1 = array();
    $query = $mysqli->query("select CONCAT('new Date(',year(mail_sends.date), ',',month(mail_sends.date),',',day(mail_sends.date),')' ), count(sends_list.id) from mail_sends, sends_list  where mail_sends.id = sends_list.sid group by mail_sends.date order by mail_sends.date");
    while ($row =  $query->fetch_row()) {
        $str = "[$row[0],$row[1]],";
        array_push($arr1, $str);
        $i++;
    }
    $str1 = implode("", $arr1); //echo $str1;
    ?>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['calendar']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Дата', 'Разослано сообщений'],
                <?php echo $str1 ?>
            ]);
            var options = {
                title: 'Статистика рассылок',
                noDataPattern: {
                    backgroundColor: '#76a7fa',
                    color: '#a0c3ff'
                },
                legend: {
                    position: 'bottom'
                },
            };
            var chart = new google.visualization.Calendar(document.getElementById('curve_chart3'));
            chart.draw(data, options);
        }
    </script>
    <div id="curve_chart3" style="width: 1400px; height: 400px"></div>
    <h1>Задание 4. Организационная диаграмма</h1>
    <h2>Соотношение типов и жанров произведений</h2>
    <?php
    $query0 = $mysqli->query("select affiliation.tid, types.title from affiliation, types where types.id=affiliation.tid group by tid order by tid");
    $i = 0;
    while ($row0 =  $query0->fetch_row()) {
        $arr1 = array();
        $str = "['$row0[1]','',''],";
        array_push($arr1, $str);
        $query = $mysqli->query("select genres.name, types.title from genres,types, affiliation where affiliation.gid=genres.id and affiliation.tid=types.id and types.id=$row0[0] group by affiliation.id  ");

        while ($row =  $query->fetch_row()) {
            $str = "['$row[0]','$row[1]',''],";
            array_push($arr1, $str);
        }
        $str1 = implode("", $arr1);
        //echo $str1;
    ?>
        <script type="text/javascript">
            google.charts.load('current', {
                'packages': ['orgchart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Жанр', 'Тип произведения', ''],
                    <?php echo $str1 ?>
                ]);
                var options = {
                    title: 'Соотношение типов и жанров произведений',
                    noDataPattern: {
                        backgroundColor: '#76a7fa',
                        color: '#a0c3ff'
                    },
                    legend: {
                        position: 'bottom'
                    },
                    compactRows: true,
                    size: 'small',
                };
                var chart = new google.visualization.OrgChart(document.getElementById('curve_chart4<?php echo $i; ?>'));
                data.setRowProperty(0, 'style', 'border: 3px solid green');
                data.setRowProperty(0, 'selectedStyle', 'background-color:#0000FF');
                chart.draw(data, options);
            }
        </script>

        <div id="curve_chart4<?php echo $i; ?>" style="width: 100%; height: 100px"></div>
    <?php
        $i++;
    }
    ?>
    <h1>Задание 5. Карты</h1>
    <h2>Карта динамики выхода произведений</h2>
    <img src="pic/карта.gif" style='width:60%; margin-left: auto;display: block; margin-right: auto;'>
    <?php
    $arr1 = array();

    ?>



    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"> </script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=db4c72d5-0250-4ef8-818d-5feec90bd800&lang=ru_RU" type="text/javascript"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart', 'sankey', 'map', 'timeline', 'calendar', 'orgchart'],
            'mapsApiKey': 'AIzaSyD-9tSrke72PouQMnMX-a7eZSW0jkFMBWY'
        });
        ymaps.ready(init);

        function init() {
            let map = new ymaps.Map('map', {
                center: [0, 0],
                zoom: 1,
            });
            map.controls.remove('geolocationControl');
            map.controls.remove('searchControl');
            map.controls.remove('trafficControl');
            map.controls.remove('typeSelector');
            map.controls.remove('fullscreenControl');
            map.controls.remove('zoomControl');
            map.controls.remove('rulerCOntrol');

            <?php
            $countrys = $mysqli->query("select DISTINCT country from media");
            while ($country = $countrys->fetch_row()) {
                $query = $mysqli->query("select count(id) from media where country = '$country[0]'");
                while ($row =  $query->fetch_row()) {
                    $count = $row[0];
            ?>
                    console.log(<? echo $count; ?>);
                    ymaps.geocode(<? echo "'" . $country[0] . "'" ?>, {
                        results: 1
                    }).then(function(res) {
                        var firstGeoObject = res.geoObjects.get(0),
                            coords = firstGeoObject.geometry.getCoordinates(),
                            bounds = firstGeoObject.properties.get('boundedBy');
                        firstGeoObject.options.set('preset', 'islands#darkBlueDotIconWithCaption');
                        firstGeoObject.properties.set('iconCaption', firstGeoObject.getAddressLine() + '. Кол-во: <? echo $count; ?>');
                        firstGeoObject.properties.set('balloonContentHeader', ' Количество');
                        firstGeoObject.properties.set('balloonContentBody', ' <p> произведений: <? echo $count; ?> </p>');
                        map.geoObjects.add(firstGeoObject);
                    });
            <? }
            } ?>
        }
    </script>
    <div id="map" class="mb-3" style="width: 1200px; height: 400px;"></div>
    <h1>Задание 6. Диаграмма Санки</h1>
    <h2>Соотношение жанров и типов произведений</h2>
    <?php
    $arr1 = array();
    $query = $mysqli->query("select genres.name, types.title from genres,types, affiliation where affiliation.gid=genres.id and affiliation.tid=types.id  group by affiliation.id");
    while ($row =  $query->fetch_row()) {
        $str = "['$row[0]','$row[1]',1],";
        array_push($arr1, $str);
    }
    $str1 = implode("", $arr1);
    ?>
    <script type="text/javascript">
        google.charts.load("current", {
            packages: ["sankey"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'From');
            data.addColumn('string', 'To');
            data.addColumn('number', '');
            data.addRows([
                <?php echo $str1 ?>
            ]);

            // Set chart options
            var options = {
                width: 600,
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.Sankey(document.getElementById('curve_chart6'));
            chart.draw(data, options);
        }
    </script>
    <div id="curve_chart6" style="width: 100%; height: 2000px"></div>
    <h1>Задание 7. Хронология</h1>
    <h2>Хронология рассылок</h2>
    <?php
    $arr1 = array();
    $query = $mysqli->query("select CONCAT('new Date(',year(mail_sends.date), ',',month(mail_sends.date),',',day(mail_sends.date),')' ), theme_sends.them from mail_sends, sends_list, theme_sends where mail_sends.id = sends_list.sid and mail_sends.them=theme_sends.id group by mail_sends.date order by mail_sends.date");
    if ($row =  $query->fetch_row()) {
        $str = "['$row[1]',$row[0],";
        array_push($arr1, $str);
    }

    while ($row =  $query->fetch_row()) {
        $str = "$row[0]],['$row[1]',$row[0],";
        array_push($arr1, $str);
    }
    $str = "new Date" . date('(Y,m,d)') . "]";
    array_push($arr1, $str);
    $str1 = implode("", $arr1); //echo $str1
    ?>

    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['timeline']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var container = document.getElementById('timeline');
            var chart = new google.visualization.Timeline(container);
            var dataTable = new google.visualization.DataTable();

            dataTable.addColumn({
                type: 'string',
                id: 'President'
            });
            dataTable.addColumn({
                type: 'date',
                id: 'Start'
            });
            dataTable.addColumn({
                type: 'date',
                id: 'End'
            });
            dataTable.addRows([
                <?php echo $str1 ?>
            ]);

            chart.draw(dataTable);
        }
    </script>

    <div id="timeline" style="height: 180px;"></div>

</body>

</html>
<?php ob_end_flush(); ?>