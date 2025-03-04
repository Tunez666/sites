<?php
require 'connect.php'; // Подключаем подключение к БД

function fetchTable($link, $tableName) {
    $query = "SELECT * FROM `$tableName`";
    $result = mysqli_query($link, $query);

    if (!$result) {
        echo "<p>Ошибка запроса к таблице $tableName: " . mysqli_error($link) . "</p>";
        return;
    }

    echo "<h2>Таблица: $tableName</h2>";
    echo "<table border='1'>";
    echo "<tr>";

    // Вывод заголовков таблицы
    $fields = mysqli_fetch_fields($result);
    foreach ($fields as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";

    // Вывод строк
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>{$value}</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вывод таблиц</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            display: block;
            margin-top: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <h1>Вывод всех таблиц</h1>

    <?php
        fetchTable($Link, "reader"); // Замените на реальные названия таблиц
        fetchTable($Link, "book");
        fetchTable($Link, "regist");
        mysqli_close($Link);
    ?>

    <a href="index.php">Вернуться на главную</a>

</body>
</html>
