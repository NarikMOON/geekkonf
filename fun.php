<?php
function HipoTreng($array)
{
    $array = array_values($array);
    echo 'Проверка гипотезы по критерию основанному на медиане<br>';
    //var_dump($array);
    $sortarray = $array;
    sort($sortarray);
    $length = count($array);
    if ($length % 2 == 0) $median = ($sortarray[(int)$length / 2 - 1] + $sortarray[(int)$length / 2]) / 2;
    else if ($length % 2 == 1) $median = $sortarray[(int)($length / 2)];
    echo 'Медиана: ' . $median . '<br>ẟ:';
    $tr1 = '<tr><td>Период</td>';
    $tr2 = '<tr><td>Значение</td>';
    $tr3 = '<tr><td>ẟ:</td>';
    $deltat = array();
    for ($i = 0; $i < $length; $i++) {
        if ($array[$i] > $median) {
            array_push($deltat, 1);
            echo '+';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . $array[$i] . '</td>';
            $tr3 .= '<td>+</td>';
        } else if ($array[$i] < $median) {
            array_push($deltat, 0);
            echo '-';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . $array[$i] . '</td>';
            $tr3 .= '<td>-</td>';
        } else {
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . $array[$i] . '</td>';
            $tr3 .= '<td></td>';
        }
    }
    $tr1 .= '</tr>';
    $tr2 .= '</tr>';
    $tr3 .= '</tr>';
    echo '<br><table border="1" class="tab1">' . $tr1 . $tr2 . $tr3 . '</table>';
    //var_dump($array);
    //echo var_dump($deltat);
    $vn = 1;
    $tmax = -1;
    $tlength = 1;
    for ($i = 0; $i < count($deltat) - 1; $i++) {
        if ($deltat[$i] == $deltat[$i + 1]) {
            $tlength++;
        } else {
            if ($i != count($deltat) - 1) $vn++;
            if ($tlength > $tmax) {
                $tmax = $tlength;
                $tlength = 1;
            }
        }
    }
    if ($tmax == -1) $tmax = count($deltat);
    echo '<br>Серий: ' . $vn . ', наибольшая длина серии: ' . $tmax . '<br>';
    $numsr1 = (int)(($length + 1 - 1.96 * sqrt($length - 1)) / 2);
    $numsr2 = (int)(1.431 * log($length + 1));
    echo  'Сравниваемое с числом серий: ' . $numsr1 . ', сравниваемое с наибольшей длиной серии: ' . $numsr2 . '<br>';
    if (($vn > $numsr1) and ($tmax < $numsr2)) {
        echo 'Гипотеза принимается по критерию на основе медианы!';
    } else {
        echo 'Гипотеза отвергается по критерию на основе медианы!';
    }
    //////--------------------------------------------------------------
    echo '<br>Проверка гипотезы по критерию "восходящих и нисходящих серий"<br>';
    echo 'ẟ:';
    $deltat = array();
    $tr1 = '<tr><td>Период</td>';
    $tr2 = '<tr><td>Значение</td>';
    $tr3 = '<tr><td>ẟ:</td>';
    for ($i = 0; $i < $length - 1; $i++) {
        if (($array[$i + 1] - $array[$i]) > 0) {
            array_push($deltat, 1);
            echo '+';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . ($array[$i + 1] - $array[$i]) . '</td>';
            $tr3 .= '<td>+</td>';
        } else if (($array[$i + 1] - $array[$i]) < 0) {
            array_push($deltat, 0);
            echo '-';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . ($array[$i + 1] - $array[$i]) . '</td>';
            $tr3 .= '<td>-</td>';
        } else {
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . ($array[$i + 1] - $array[$i]) . '</td>';
            $tr3 .= '<td></td>';
        }
    }
    $tr1 .= '</tr>';
    $tr2 .= '</tr>';
    $tr3 .= '</tr>';
    echo '<br><table border="1" class="tab1">' . $tr1 . $tr2 . $tr3 . '</table>';
    $vn = 1;
    $tmax = -1;
    $tlength = 1;
    for ($i = 0; $i < count($deltat) - 1; $i++) {
        if ($deltat[$i] == $deltat[$i + 1]) {
            $tlength++;
        } else {
            if ($i != count($deltat) - 1) $vn++;
            if ($tlength > $tmax) {
                $tmax = $tlength;
                $tlength = 1;
            }
        }
    }
    if ($tmax == -1) $tmax = count($deltat);
    echo '<br>Серий: ' . $vn . ', наибольшая длина серии: ' . $tmax . '<br>';
    $numsr1 = (int)((2 * $length - 1) / 3 - 1.96 * sqrt((16 * $length - 29) / 90));
    $numsr2 = 5 + ($length > 26) + ($length > 153) + ($length > 1170);
    echo  'Сравниваемое с числом серий: ' . $numsr1 . ', сравниваемое с наибольшей длиной серии: ' . $numsr2 . '<br>';
    if (($vn > $numsr1) and ($tmax < $numsr2)) {
        echo 'Гипотеза принимается по критерию "восходящих и нисходящих серий"!';
    } else {
        echo 'Гипотеза отвергается по критерию "восходящих и нисходящих серий"!';
    }
} ////////////////////////////////////////////
function lr82($array)
{
    $array = array_values($array);
    $table = '<table border="1" class="tab1">';
    $table .= '<th>№</th><th>Yt</th><th>t</th><th>Yt*t</th><th>t^2</th><th>Yt*t^2</th><th>t^4</th><th>lnYt</th><th>ln(Yt)t</th>';
    $length = count($array);
    $midle = (int)($length / 2);
    $sum2 = 0;
    $sum4 = 0;
    $sum5 = 0;
    $sum6 = 0;
    $sum7 = 0;
    $sum8 = 0;
    $sum9 = 0;
    for ($i = 0; $i < $length; $i++) {
        $table .= '<tr><td>' . ($i + 1) . '</td><td>' . $array[$i] . '</td><td>' . ($i - $midle) . '</td><td>' . $array[$i] * ($i - $midle) . '</td><td>' . ($i - $midle) * ($i - $midle) . '</td><td>' . $array[$i] * ($i - $midle) * ($i - $midle) . '</td><td>' . ($i - $midle) * ($i - $midle) * ($i - $midle) * ($i - $midle) . '</td><td>' . number_format(log($array[$i]), 2, '.', ',') . '</td><td>' . number_format(log($array[$i]) * ($i - $midle), 2, '.', ',') . '</td></tr>';
        $sum2 += $array[$i];
        $sum4 += $array[$i] * ($i - $midle);
        $sum5 += ($i - $midle) * ($i - $midle);
        $sum6 += $array[$i] * ($i - $midle) * ($i - $midle);
        $sum7 += ($i - $midle) * ($i - $midle) * ($i - $midle) * ($i - $midle);
        $sum8 += log($array[$i]);
        $sum9 += log($array[$i]) * ($i - $midle);
    }
    $table .= '<tr><td>Суммы</td><td>' . $sum2 . '</td><td></td><td>' . $sum4 . '</td><td>' . $sum5 . '</td><td>' . $sum6 . '</td><td>' . $sum7 . '</td><td>' . number_format($sum8, 2, '.', ',') . '</td><td>' . number_format($sum9, 2, '.', ',') . '</td></tr>';

    if ($length > 1) {

        $a1 = $sum2 / $length;
        $b1 = $sum4 / $sum5;
        $expr1 = 'y=(' . $a1 . ')+(' . $b1 . 't)';
        $c2 = ($length * $sum6 - $sum5 * $sum2) / ($length * $sum7 - $sum5 * $sum5);
        $a2 = $a1 - ($sum5 / $length) * $c2;
        $b2 = $b1;
        $expr2 = 'y=(' . $a2 . ')+(' . $b2 . 't)+(' . $c2 . 't^2)';
        $a3 = exp($sum8 / $length);
        $b3 = exp($sum9 / $sum5);
        $expr3 = 'y=(' . $a3 . ')*(' . $b3 . '^t)';
        $arr2 = array();
        $str = '["Период","Реальное значение","Линейная модель","Параболическая модель","Показательная модель"],';
        array_push($arr2, $str);
        $e_line = array();
        $e_exp = array();
        $e_x2 = array();
        for ($i = 0; $i < $length; $i++) {
            $str = '[' . ($i + 1) . ',' . number_format($array[$i], 2, '.', ',') . ',' . number_format($a1 + $b1 * ($i - $midle), 2, '.', ',') . ',' . number_format($a2 + $b2 * ($i - $midle) + $c2 * ($i - $midle) * ($i - $midle), 2, '.', ',') . ',' . number_format($a3 * (Pow($b3, ($i - $midle))), 2, '.', ',') . '],';
            array_push($arr2, $str);
            array_push($e_line, $array[$i] - $a1 + $b1 * ($i - $midle));
            array_push($e_exp, $array[$i] - $a2 + $b2 * ($i - $midle) + $c2 * ($i - $midle) * ($i - $midle));
            array_push($e_x2, $array[$i] - $a3 * (Pow($b3, ($i - $midle))));
        }
        $str = implode($arr2);
    } else $str = '["Период","Реальное значение","Линейная модель","Параболическая модель","Показательная модель"],' . '[' . (1) . ',' . number_format($array[0], 2, '.', ',') . ',' . number_format($array[0], 2, '.', ',') . ',' . number_format($array[0], 2, '.', ',') . ',' . number_format($array[0], 2, '.', ',') . '],';

    $table .= '</table>';

    //echo '<br>line '.var_dump($e_line);echo '<br>exp '.var_dump($e_exp);echo '<br>x2 '.var_dump($e_x2);
    $lr9 = '';
    $lr9 .= '<h3>1) Проверить, удовлетворяет ли остаточная последовательность свойству случайности колебаний уровней ряда (с помощью критерия знаков из предыдущей работы)</h3>';
    $lr9 .= '<h4>Проверка линейной модели</h4>' . delta91($e_line);
    $lr9 .= '<h4>Проверка показательной модели</h4>' . delta91($e_exp);
    $lr9 .= '<h4>Проверка параболической модели</h4>' . delta91($e_x2);


    //92
    $lr9 .= '<h3>2) Проверить, удовлетворяет ли остаточная последовательность свойству соответствия распределения нормальному закону (с помощью коэффициентов асимметрии и эксцесса)</h3>';
    $res_e_line = norm92($e_line);
    $res_e_exp = norm92($e_exp);
    $res_e_x2 = norm92($e_x2);
    $lr9 .=  '<br>Для линейной модели:' . $res_e_line['res'];
    $lr9 .= '<br>Для показательной модели:' . $res_e_exp['res'];
    $lr9 .= '<br>Для параболической модели:' . $res_e_x2['res'] . '<br>';
    //echo $lr9;
    //93
    $lr9 .= '<h3>3) Проверить, удовлетворяет ли остаточная последовательность свойству независимости значений между собой (с помощью теста Дарбина-Уотсона)</h3>';
    $lr9 .= '<br><h4>Для линейной модели:</h4>' . darbin93($array, $e_line, 'line');
    $lr9 .= '<br><h4>Для показательной модели:</h4>' . darbin93($array, $e_exp, 'pokaz');
    $lr9 .= '<br><h4>Для параболической модели:</h4>' . darbin93($array, $e_x2, 'parab');
    //94
    $lr9 .= '<h3>4) Вычислить показатели точности модели (MAPE, S, SSE, MSE). Сделать вывод о том, какая из моделей лучше описывает исходные данные.</h3>';
    $lr9 .= lr94($array, $a1, $b1, $a3, $b3, $a2, $c2);




    $newarr = array(
        "table" => $table,
        "data" => $str,
        "expr1" => $expr1,
        "expr2" => $expr2,
        "expr3" => $expr3,
        "lr9" => $lr9
    );
    return $newarr;
}
function delta91($array)
{
    $array = array_values($array);
    $lr91 = 'Проверка гипотезы по критерию основанному на медиане<br>';
    //var_dump($array);
    $sortarray = $array;
    sort($sortarray);
    $length = count($array);
    if ($length % 2 == 0) $median = ($sortarray[(int)$length / 2 - 1] + $sortarray[(int)$length / 2]) / 2;
    else if ($length % 2 == 1) $median = $sortarray[(int)($length / 2)];
    $lr91 .= 'Медиана: ' . $median . '<br>ẟ:';
    $tr1 = '<tr><td>Период</td>';
    $tr2 = '<tr><td>Значение</td>';
    $tr3 = '<tr><td>ẟ:</td>';
    $deltat = array();
    for ($i = 0; $i < $length; $i++) {
        if ($array[$i] > $median) {
            array_push($deltat, 1);
            $lr91 .= '+';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i], 2) . '</td>';
            $tr3 .= '<td>+</td>';
        } else if ($array[$i] < $median) {
            array_push($deltat, 0);
            $lr91 .= '-';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i], 2) . '</td>';
            $tr3 .= '<td>-</td>';
        } else {
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i], 2) . '</td>';
            $tr3 .= '<td></td>';
        }
    }
    $tr1 .= '</tr>';
    $tr2 .= '</tr>';
    $tr3 .= '</tr>';
    $lr91 .= '<br><table border="1" class="tab1">' . $tr1 . $tr2 . $tr3 . '</table>';
    //var_dump($array);
    //echo var_dump($deltat);
    $vn = 1;
    $tmax = -1;
    $tlength = 1;
    for ($i = 0; $i < count($deltat) - 1; $i++) {
        if ($deltat[$i] == $deltat[$i + 1]) {
            $tlength++;
        } else {
            if ($i != count($deltat) - 1) $vn++;
            if ($tlength > $tmax) {
                $tmax = $tlength;
                $tlength = 1;
            }
        }
    }
    if ($tmax == -1) $tmax = count($deltat);
    $lr91 .= '<br>Серий: ' . $vn . ', наибольшая длина серии: ' . $tmax . '<br>';
    $numsr1 = (int)(($length + 1 - 1.96 * sqrt($length - 1)) / 2);
    $numsr2 = (int)(1.431 * log($length + 1));
    $lr91 .=  'Сравниваемое с числом серий: ' . $numsr1 . ', сравниваемое с наибольшей длиной серии: ' . $numsr2 . '<br>';
    if (($vn > $numsr1) and ($tmax < $numsr2)) {
        $lr91 .= 'Гипотеза принимается по критерию на основе медианы!';
    } else {
        $lr91 .= 'Гипотеза отвергается по критерию на основе медианы!';
    }
    //////--------------------------------------------------------------
    $lr91 .= '<br>Проверка гипотезы по критерию "восходящих и нисходящих серий"<br>';
    $lr91 .= 'ẟ:';
    $deltat = array();
    $tr1 = '<tr><td>Период</td>';
    $tr2 = '<tr><td>Значение</td>';
    $tr3 = '<tr><td>ẟ:</td>';
    for ($i = 0; $i < $length - 1; $i++) {
        if (($array[$i + 1] - $array[$i]) > 0) {
            array_push($deltat, 1);
            $lr91 .= '+';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i + 1] - $array[$i], 2) . '</td>';
            $tr3 .= '<td>+</td>';
        } else if (($array[$i + 1] - $array[$i]) < 0) {
            array_push($deltat, 0);
            $lr91 .= '-';
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i + 1] - $array[$i], 2) . '</td>';
            $tr3 .= '<td>-</td>';
        } else {
            $tr1 .= '<th>' . ($i + 1) . '</th>';
            $tr2 .= '<td>' . round($array[$i + 1] - $array[$i], 2) . '</td>';
            $tr3 .= '<td></td>';
        }
    }
    $tr1 .= '</tr>';
    $tr2 .= '</tr>';
    $tr3 .= '</tr>';
    $lr91 .= '<br><table border="1" class="tab1">' . $tr1 . $tr2 . $tr3 . '</table>';
    $vn = 1;
    $tmax = -1;
    $tlength = 1;
    for ($i = 0; $i < count($deltat) - 1; $i++) {
        if ($deltat[$i] == $deltat[$i + 1]) {
            $tlength++;
        } else {
            if ($i != count($deltat) - 1) $vn++;
            if ($tlength > $tmax) {
                $tmax = $tlength;
                $tlength = 1;
            }
        }
    }
    if ($tmax == -1) $tmax = count($deltat);
    $lr91 .= '<br>Серий: ' . $vn . ', наибольшая длина серии: ' . $tmax . '<br>';
    $numsr1 = (int)((2 * $length - 1) / 3 - 1.96 * sqrt((16 * $length - 29) / 90));
    $numsr2 = 5 + ($length > 26) + ($length > 153) + ($length > 1170);
    $lr91 .=  'Сравниваемое с числом серий: ' . $numsr1 . ', сравниваемое с наибольшей длиной серии: ' . $numsr2 . '<br>';
    if (($vn > $numsr1) and ($tmax < $numsr2)) {
        $lr91 .= 'Гипотеза принимается по критерию "восходящих и нисходящих серий"!';
    } else {
        $lr91 .= 'Гипотеза отвергается по критерию "восходящих и нисходящих серий"!';
    }
    return $lr91;
}

function norm92($array)
{
    $length = count($array);
    $e2 = 0;
    $e3 = 0;
    $e4 = 0;

    for ($i = 0; $i < $length; $i++) {
        $e2 += $array[$i] * $array[$i];
        $e3 += $array[$i] * $array[$i] * $array[$i];
        $e4 += $array[$i] * $array[$i] * $array[$i] * $array[$i];
    }

    $A = ($e3 / $length) / (Pow($e2 / $length, 1 / 3));
    //echo var_dump($A);
    $E = ($e4 / $length) / (Pow($e2 / $length, 1 / 3)) - 3;
    //echo var_dump($E);
    $test1 = 1.5 * Sqrt((6 * ($length - 2)) / (($length + 1) * ($length + 3)));
    $Abool = abs($A) < $test1;
    $test2 = 1.5 * Sqrt((24 * $length * ($length - 2) * ($length - 3)) / (($length + 1) * ($length + 1) * ($length + 3) * ($length + 5)));
    $Ebool = abs($E + 6 / ($length + 1)) < $test2;
    $bool = $Abool and $Ebool;
    //echo var_dump($Abool).var_dump($Ebool).var_dump($bool);
    $test3 = 2 * Sqrt((6 * ($length - 2)) / (($length + 1) * ($length + 3)));
    $Abool2 = abs($A) >= $test3;
    $test4 = 2 * Sqrt((24 * $length * ($length - 2) * ($length - 3)) / (($length + 1) * ($length + 1) * ($length + 3) * ($length + 5)));
    $Ebool2 = abs($E + 6 / ($length + 1)) >= $test4;
    $bool2 = $Abool2 or $Ebool2;
    //echo var_dump($Abool2).var_dump($Ebool2).var_dump($bool2);
    $str = "<br>A = $A<br>Э = $E<br>";
    if ($bool) {
        $str .= 'Остаточная последовательность удовлетворяет свойству соответствия распределения нормальному закону. |A| < ' . $test1 . ' и |Э + (6/(n+1))| < ' . $test2 . '';
    } else if ($bool2) {
        $str .= 'Остаточная последовательность не удовлетворяет свойству соответствия распределения нормальному закону. |A| >= ' . $test3 . ' или |Э + (6/(n+1))| >= ' . $test4 . '';
    } else $str .= 'Нельзя судить, удовлетворяет ли остаточная последовательность свойству соответствия распределения нормальному закону';



    $newarr = array(
        "A" => $A,
        "E" => $E,
        "res" => $str
    );
    return $newarr;
}
function darbin93($array, $e, $model)
{

    $lr93 = "<div><h4>Тест Дарбина-Уотсона</h4></div>";
    $lr93 .= "<table border='1' class='tab1'>
    <tr><th>№</th> <th>y(t)</th> <th>t</th> <th>y^(t)</th> <th>e(t)</th> <th>(e(t))^2</th> <th>(e(t)-e(t-1))^2</th> </tr> 
    ";
    $length = count($array);
    $y = $array;
    $line_model_ostatki_base = $e;
    $i = 0;
    $t = (int)($length / 2) - $length;
    $sum_et2 = 0;
    $sum_et2_et1_2 = 0;
    $y_sp = array();

    while ($i < count($y)) {
        $number = $i + 1;
        $col4 = round($y[$i] - $line_model_ostatki_base[$i], 4);
        $col6 = round($line_model_ostatki_base[$i] * $line_model_ostatki_base[$i], 4);
        $sum_et2 =  $sum_et2 + $col6;
        if ($i > 0) {
            $col7 = round(pow($line_model_ostatki_base[$i] - $line_model_ostatki_base[$i - 1], 2), 4);
            $sum_et2_et1_2 = $sum_et2_et1_2 + $col7;
        } else {
            $col7 = "-";
        }
        $lr93 .= "<tr><td>$number</td><td>$y[$i]</td><td>$t</td><td>$col4</td><td>$line_model_ostatki_base[$i]</td><td>$col6</td><td>$col7</td></tr>
        ";
        $i = $i + 1;
        $t = $t + 1;
    }
    $lr93 .= "<tr><th>Sum</th><td> </td><td> </td><td> </td><td> </td><td>$sum_et2</td><td>$sum_et2_et1_2</td></tr>
    </table>
    ";
    switch ($model) {
        case "line":
            $dl = 0.812;
            $du = 1.576;
            break;

        case "pokaz":
            $dl = 0.812;
            $du = 1.576;
            break;

        case "parab":
            $dl = 0.685;
            $du = 1.864;
            break;
    }

    $d = round($sum_et2_et1_2 / $sum_et2, 3);
    $lr93 .= "<br>d = $d";
    $lr93 .= "<br>dl = $dl";
    $lr93 .= "<br>du = $du";

    if (($d >= $dl) && ($d <= $du)) {
        $lr93 .= "<br>Значение d попало в область неопределенности";
    } else {
        if ($d < $dl) {
            $lr93 .= "<br>Гипотеза об отсутствии автокорреляции отвергается";
        }
        if ($d > $du) {
            $lr93 .= "<br>Гипотеза об отсутствии автокорреляции принимается";
        }
    }
    return $lr93;
}
function lr94($y, $a0_l, $a1_l, $a0_pok, $a1_pok, $a0_p, $a2_p)
{
    $line_test = array();
    $parab_test = array();
    $pokaz_test = array();
    $i = 0;
    $t = -1 * ((count($y) - 1) / 2);
    while ($i < count($y)) {
        $arg_l = $a0_l + $a1_l * $t;
        array_push($line_test,   round($arg_l, 4));
        $arg_pok = $a0_pok * pow($a1_pok, $t);
        array_push($pokaz_test,   round($arg_pok, 4));
        $arg_p = $a0_p + $a1_l * $t + $a2_p * ($t * $t);
        array_push($parab_test,   round($arg_p, 4));
        $t = $t + 1;
        $i = $i + 1;
    }


    //Линейная
    $str = "<h4>Линейная модель</h4>";
    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + abs(($line_test[$i] - $y[$i]) / $y[$i]);
        $i = $i + 1;
    }
    $MAPE = round(100 * ($sum_mape / count($y)), 4);
    $str .= "<br>MAPE = $MAPE";
    if ($MAPE < 10) {
        $str .= "<br>Так как $MAPE < 10%, модель имеет высокую точность";
    }
    if ($MAPE >= 10 && $MAPE <= 20) {
        $str .= "<br>Так как 10% <= $MAPE <= 20%, модель можно считать хорошей";
    }
    if ($MAPE > 20 && $MAPE < 50) {
        $str .= "<br>Так как 20% < $MAPE < 50%, модель можно считать удовлетворительной";
    }
    if ($MAPE >= 50) {
        $str .= "<br>Так как $MAPE > 50%, модель можно считать плохой";
    }
    $MAPE_l = $MAPE;

    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + pow(($line_test[$i] - $y[$i]), 2);
        $i = $i + 1;
    }
    $S = round(sqrt(($sum_mape / count($y))), 4);
    $SSE = round($sum_mape, 4);
    $MSE =  round($SSE / (count($y) - 2), 2);
    $str .= "<br>S = $S";
    $str .= "<br>SSE = $SSE";
    $str .= "<br>MSE = $MSE";


    //Показательная
    $str .= "<h4>Показательная модель</h4>";
    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + abs(($pokaz_test[$i] - $y[$i]) / $y[$i]);
        $i = $i + 1;
    }
    $MAPE = round(100 * ($sum_mape / count($y)), 4);
    $str .= "<br>MAPE = $MAPE";
    if ($MAPE < 10) {
        $str .= "<br>Так как $MAPE < 10%, модель имеет высокую точность";
    }
    if ($MAPE >= 10 && $MAPE <= 20) {
        $str .= "<br>Так как 10% <= $MAPE <= 20%, модель можно считать хорошей";
    }
    if ($MAPE > 20 && $MAPE < 50) {
        $str .= "<br>Так как 20% < $MAPE < 50%, модель можно считать удовлетворительной";
    }
    if ($MAPE >= 50) {
        $str .= "<br>Так как $MAPE > 50%, модель можно считать плохой";
    }
    $MAPE_pokaz = $MAPE;

    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + pow(($pokaz_test[$i] - $y[$i]), 2);
        $i = $i + 1;
    }
    $S = round(sqrt(($sum_mape / count($y))), 4);
    $SSE = round($sum_mape, 4);
    $MSE =  round($SSE / (count($y) - 2), 2);
    $str .= "<br>S = $S";
    $str .= "<br>SSE = $SSE";
    $str .= "<br>MSE = $MSE";

    //Параболическая
    $str .= "<h4>Параболическая модель</h4>";
    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + abs(($parab_test[$i] - $y[$i]) / $y[$i]);
        $i = $i + 1;
    }
    $MAPE = round(100 * ($sum_mape / count($y)), 4);
    $str .= "<br>MAPE = $MAPE";
    if ($MAPE < 10) {
        $str .= "<br>Так как $MAPE < 10%, модель имеет высокую точность";
    }
    if ($MAPE >= 10 && $MAPE <= 20) {
        $str .= "<br>Так как 10% <= $MAPE <= 20%, модель можно считать хорошей";
    }
    if ($MAPE > 20 && $MAPE < 50) {
        $str .= "<br>Так как 20% < $MAPE < 50%, модель можно считать удовлетворительной";
    }
    if ($MAPE >= 50) {
        $str .= "<br>Так как $MAPE > 50%, модель можно считать плохой";
    }
    $MAPE_parab = $MAPE;

    $i = 0;
    $sum_mape = 0;
    while ($i < count($y)) {
        $sum_mape = $sum_mape + pow(($parab_test[$i] - $y[$i]), 2);
        $i = $i + 1;
    }
    $S = round(sqrt(($sum_mape / count($y))), 4);
    $SSE = round($sum_mape, 4);
    $MSE =  round($SSE / (count($y) - 2), 2);
    $str .= "<br>S = $S";
    $str .= "<br>SSE = $SSE";
    $str .= "<br>MSE = $MSE";
    $str .= "<br>Результат: ";
    if ($MAPE_l < $MAPE_parab) {
        if ($MAPE_l < $MAPE_pokaz) {
            $str .= "<br>Линейная модель является самой точной";
        } else {
            $str .= "<br>Показательная модель является самой точной";
        }
    } else {
        if ($MAPE_parab < $MAPE_pokaz) {
            $str .= "<br>Параболическая модель является самой точной";
        } else {
            $str .= "<br>Показательная модель является самой точной";
        }
    }
    return $str;
}
