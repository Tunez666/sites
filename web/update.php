<?php
require 'connect.php';

// Получаем список всех номеров читательских билетов
$bill_nums_sql = "SELECT bill_num FROM reader ORDER BY bill_num";
$bill_nums_result = $Link->query($bill_nums_sql);

// Определяем текущий ID читательского билета
$current_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Если ID выбран, получаем данные читателя
if ($current_id) {
    $reader_sql = "SELECT * FROM reader WHERE bill_num = $current_id";
    $reader_result = $Link->query($reader_sql);
    $reader = $reader_result->fetch_assoc();
}

// Обработка формы обновления данных
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $current_id = $_POST['bill_num'];

    $update_sql = "UPDATE reader SET fio = '$name', adres = '$address', phone = '$phone' WHERE bill_num = $current_id";

    if ($Link->query($update_sql) === TRUE) {
        echo "<p>Данные читателя успешно обновлены!</p>";
        // Обновляем данные читателя после успешного обновления
        $reader_result = $Link->query($reader_sql);
        $reader = $reader_result->fetch_assoc();
    } else {
        echo "<p>Ошибка: " . $Link->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Изменить данные читателя</title>
</head>
<body>
    <h2>Изменить данные читателя</h2>
    
    <form method="get">
        <label for="id">Номер читательского билета:</label>
        <select id="id" name="id" onchange="this.form.submit()">
            <option value="">Выберите номер билета</option>
            <?php while($row = $bill_nums_result->fetch_assoc()): ?>
                <option value="<?php echo $row['bill_num']; ?>" <?php if($current_id == $row['bill_num']) echo 'selected'; ?>>
                    <?php echo $row['bill_num']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
    
    <?php if ($reader): ?>
    <form method="post">
        <input type="hidden" name="bill_num" value="<?php echo $reader['bill_num']; ?>">
        
        <div>
            <label for="name">ФИО:</label><br>
            <input type="text" id="name" name="name" value="<?php echo $reader['fio']; ?>"><br><br>
        </div>
        
        <div>
            <label for="address">Адрес:</label><br>
            <input type="text" id="address" name="address" value="<?php echo $reader['adres']; ?>"><br><br>
        </div>
        
        <div>
            <label for="phone">Телефон:</label><br>
            <input type="text" id="phone" name="phone" value="<?php echo $reader['phone']; ?>"><br><br>
        </div>

        <button type="submit">Сохранить изменения</button>
    </form>
    <?php endif; ?>
    
    <br>
    <a href="index.php">Вернуться на главную</a>
</body>
</html>

