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

// Обработка удаления читателя
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $delete_id = $_POST['bill_num'];
    
    // Проверяем, есть ли у читателя книги на руках
    $check_books_sql = "SELECT COUNT(*) as book_count FROM regist WHERE bill_num = $delete_id AND fact_vozvr IS NULL";
    $check_result = $Link->query($check_books_sql);
    $has_books = $check_result->fetch_assoc()['book_count'] > 0;
    
    if ($has_books) {
        echo "<p style='color: red;'>Ошибка: Невозможно удалить читателя, у которого есть книги на руках.</p>";
    } else {
        $delete_sql = "DELETE FROM reader WHERE bill_num = $delete_id";
        
        if ($Link->query($delete_sql) === TRUE) {
            echo "<p style='color: green;'>Читатель успешно удален!</p>";
            // Обновляем список читателей после удаления
            $bill_nums_result = $Link->query($bill_nums_sql);
            $current_id = null;
            $reader = null;
        } else {
            echo "<p style='color: red;'>Ошибка при удалении: " . $Link->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удалить читателя</title>
    <style>
        .reader-info {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #ff0000;
        }
    </style>
</head>
<body>
    <h2>Удалить читателя</h2>
    
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
    <div class="reader-info">
        <h3>Информация о читателе</h3>
        <p><strong>Номер билета:</strong> <?php echo $reader['bill_num']; ?></p>
        <p><strong>ФИО:</strong> <?php echo $reader['fio']; ?></p>
        <p><strong>Адрес:</strong> <?php echo $reader['adres']; ?></p>
        <p><strong>Телефон:</strong> <?php echo $reader['phone']; ?></p>
    </div>
    
    <form method="post" onsubmit="return confirm('Вы уверены, что хотите удалить этого читателя?');">
        <input type="hidden" name="bill_num" value="<?php echo $reader['bill_num']; ?>">
        <button type="submit" name="delete" class="delete-btn">Удалить читателя</button>
    </form>
    <?php elseif ($current_id): ?>
    <p>Читатель с номером билета <?php echo $current_id; ?> не найден.</p>
    <?php endif; ?>
    
    <br>
    <a href="index.php">Вернуться на главную</a>
    
    <script>
    // Если читатель был удален, очищаем параметр id из URL
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])): ?>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }
    <?php endif; ?>
    </script>
</body>
</html>

