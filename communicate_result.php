<?php
session_start();

?>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="accc.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Montserrat:wght@300&family=Roboto+Mono&display=swap" rel="stylesheet">
    <title>Перчатка.рус</title>
</head>
<body>
<div class="head">
        <div class="head_phone">
            <img class="phone_icon" src="/img/phone_icon.png" width="20px" alt="">
            <div class="phone_text">(+7) 999 231 12 12</div>
        </div>
        <div class="head_email">
            <img class="email_icon" src="/img/email.png" width="20px" alt="">
            <div class="email_text">perchatka_rus@mail.ru</div>
        </div>
    </div>

    
    <div class="container">
        <div class="header">
            <a class="header_logo" href="/index.php"><img src="/img/logo.png"  alt=""></a>
            <div class="nav">
            <a class="nav_link" href="/items/items.php">каталог</a>
                <a class="nav_link" href="/infromation/information.php">информация</a>
                <a class="nav_link_cart" href="/korzina/korzina.php"><img src="/img/cart.png" width="20px" alt=""></a>
            </div>
        </div>
    </div>

    <div class="container">
            <div class="table_title">Задать вопрос специалисту</div>
            <div class="orders_list">
                
                <form action="communicate_result.php" method=post>
                <?php
                    $question=$_POST['question'];

                    if (mb_strlen($question)<1)
                    {
                        echo 'Вы отправили пустое сообщение!';
                        exit;
                    }

                    $connect = mysqli_connect('localhost', 'root', 'erosab49', 'algoritm');

                    $username = $_SESSION['username'];
                    $data_user = mysqli_query($connect, "SELECT * FROM `users` WHERE `login` = '$username'");
                    $data_user = mysqli_fetch_array($data_user);

                    $name = $data_user[1];
                    $email = $data_user[5];
                    $phone = $data_user[4];

                    $addr = $email;
                    $theme = 'Вы задали вопрос специалисту на сайте Алгоритм';
                    $text = "Уважаемый ${name}, наш специалист в скором времени ответит на ваш вопрос. Спасибо за Ваше доверие. \n";
                    $text = $text."Ваш вопрос: ${question}";
                    mail($addr, $theme, $text);

                    $theme = "Новый вопрос от пользователя ${username}!";
                    $text = "Вопрос от ${name}. E-mail: ${email}. Номер телефона: ${phone}. \n \n";
                    $text = $text."${question}";
                    
                    mail('perchatka_rus@mail.ru', $theme, $text);

                    echo 'Ваш вопрос принят в обработку. Ожидайте ответа на Вашу электронную почту.';
                   
                ?>
                </form>
            </div>
    </div>

    <div class="footer">
        <div class="container">
            <div class="footer_text">Copyright @2019 <a class="footer_link" href="">ООО «Алгоритм М»</a>.</div>
            
        </div>
    </div>

</body>
</html>
