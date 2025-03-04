<?php
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bill_num = $_POST['bill_num'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // SQL-запрос для добавления нового читателя
    $sql = "INSERT INTO reader (bill_num, fio, adres, phone) VALUES ('$bill_num', '$name', '$address', '$phone')";

    if ($Link->query($sql) === TRUE) {
        echo "<p>Читатель успешно добавлен!</p>";
    } else {
        echo "<p>Ошибка: " . $Link->error . "</p>";
    }
}

// Получаем следующий доступный номер билета (оставляем для удобства)
$next_num_sql = "SELECT MAX(bill_num) + 1 as next_num FROM reader";
$result = $Link->query($next_num_sql);
$next_num = ($result->fetch_assoc())['next_num'];
if ($next_num < 1 || $next_num === null) $next_num = 1;

if (isset($Link)) {
    $Link->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить читателя</title>
</head>
<body>
    <h2>Добавить нового читателя</h2>
    <form method="post">
        <div>
            <label for="bill_num">Номер читательского билета:</label><br>
            <input type="number" id="bill_num" name="bill_num" value="<?php echo $next_num; ?>"><br><br>
        </div>
        
        <div>
            <label for="name">ФИО:</label><br>
            <input type="text" id="name" name="name"><br><br>
        </div>
        
        <div>
            <label for="address">Адрес:</label><br>
            <input type="text" id="address" name="address"><br><br>
        </div>
        
        <div>
            <label for="phone">Телефон:</label><br>
            <input type="text" id="phone" name="phone"><br><br>
        </div>

        <button type="submit">Добавить</button>
    </form>
    <br>
    <a href="index.php">Вернуться на главную</a>
</body>
</html>

