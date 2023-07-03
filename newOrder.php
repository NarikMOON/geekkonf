<?php 
    session_start();
    require_once 'connect.php';
    $username = $_SESSION['username'];
    $customerId = mysqli_query($connect, "SELECT `user_id` FROM `users` WHERE `login` = '$username'");
    $customerId = mysqli_fetch_array($customerId);
    $amount=$_SESSION['amount'];
    
    $result=mysqli_query($connect,"INSERT INTO `orders` (`user_id`, `amount`, `order_date`) VALUES('$customerId[0]', '$amount', CURRENT_DATE)");
    $order_id = mysqli_query($connect,"SELECT max(order_id) FROM `orders`");
    $order_id = mysqli_fetch_row($order_id);
    $cart = mysqli_query($connect,"SELECT * FROM `cart`");
    $items;
    while ($row = mysqli_fetch_row($cart)) {
        $result1=mysqli_query($connect,"INSERT INTO `order_items` (`order_id`, `item_id`, `quantity`) VALUES('$order_id[0]', '$row[0]', '$row[1]')");
    }

    mysqli_query($connect, "DELETE FROM `cart`");

    $data_user = mysqli_query($connect, "select * from users where user_id='$customerId[0]'");
    $data_user = mysqli_fetch_row($data_user);

    $order_id=$order_id[0];
    #$order_id=$order_id;
    $data_order = mysqli_query($connect, "select * from orders where order_id='$order_id'");
    $data_order = mysqli_fetch_row($data_order);

    $addr = $data_user[5];
    $theme = "Заказ успешно оформлен!";
    $text = "Оформлен заказ № ${order_id} \n \n";

    $date = $data_order[3];
    $amount = $data_order[2];

    $text = $text."Дата: ${date} Cумма заказа: ${amount} руб. \n";

    $data_order_items = mysqli_query($connect, "select * from order_items where order_id = $order_id");
    while ($row = mysqli_fetch_row($data_order_items))
    {
        $data_itemes=mysqli_query($connect, "select * from items where item_id=$row[1]");

        while ($row2 = mysqli_fetch_row($data_itemes))
        {
            $name = $row2[1];
            $price = $row2[3];
            $text = $text."${name}";
            #$text = $text."${name} Цена: ${price}";
        }

        $quantity = $row[2];
        $text = $text." Количество: ${quantity} \n";
    }

    mail($addr, $theme, $text);

    $theme = "Оформлен заказ № ${order_id}";
    mail('geekkonf@mail.ru', $theme, $text);

    header('Location: /account/acc.php');

?>
