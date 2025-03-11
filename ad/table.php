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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="table_d.css">
</head>
<body>
<div class="container">
    <h1>Вывод всех таблиц</h1>

    <?php
        fetchTable($Link, "auto"); 
        fetchTable($Link, "doljnost");
        fetchTable($Link, "dop_u");
        fetchTable($Link, "marks"); 
        fetchTable($Link, "sotrudniki");
        fetchTable($Link, "tarifs");
        fetchTable($Link, "vyzovy");
        mysqli_close($Link);
    ?>
    <div class="button-container">
        <button class="btn-new" onclick="location.href='index.php'">На главную</button>
    </div>
</div>
</body>
</html>
