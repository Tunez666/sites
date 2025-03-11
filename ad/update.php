<?php
require_once "connect.php";

// Функция для получения списка таблиц
function getTables($Link) {
    $tables = [];
    $sql = "SHOW TABLES";
    $result = $Link->query($sql);
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    return $tables;
}

// Функция для определения ID-колонки таблицы
function getIdColumn($table) {
    // Маппинг таблиц к их ID-колонкам на основе ER-модели
    $idMapping = [
        'sotrudniki' => 'id_sotr',
        'doljnost' => 'id_dol',
        'dop_u' => 'id_dop',
        'vyzovy' => 'id_v',
        'tarifs' => 'id_t',
        'marks' => 'id_m',
        'auto' => 'id_a'
    ];
    
    // Если таблица есть в маппинге, возвращаем соответствующий ID
    if (isset($idMapping[$table])) {
        return $idMapping[$table];
    }
    
    // Если таблицы нет в маппинге, используем стандартный формат
    return "id_" . substr($table, 0, 1);
}

// Функция для получения списка записей из выбранной таблицы
function getRecords($Link, $table) {
    $records = [];
    $id_column = getIdColumn($table); // Используем новую функцию
    
    $sql = "SELECT $id_column FROM $table ORDER BY $id_column";
    $result = $Link->query($sql);
    
    // Проверка успешности выполнения запроса
    if ($result === false) {
        echo "<p>Ошибка SQL: " . $Link->error . "</p>";
        return [$id_column, []]; // Возвращаем пустой массив записей
    }
    
    while ($row = $result->fetch_assoc()) {
        $records[] = $row[$id_column];
    }
    return [$id_column, $records];
}

// Функция для получения данных одной записи
function getRecordData($Link, $table, $id_column, $id) {
    $sql = "SELECT * FROM $table WHERE $id_column = $id";
    $result = $Link->query($sql);
    
    // Проверка успешности выполнения запроса
    if ($result === false) {
        echo "<p>Ошибка SQL: " . $Link->error . "</p>";
        return [];
    }
    
    return $result->fetch_assoc();
}

// Функция для обновления записи
function updateRecord($Link, $table, $id_column, $id, $postData) {
    $update_parts = [];
    foreach ($postData as $key => $value) {
        if ($key !== "table" && $key !== "id") {
            $update_parts[] = "$key = '" . $Link->real_escape_string($value) . "'";
        }
    }
    $update_sql = "UPDATE $table SET " . implode(", ", $update_parts) . " WHERE $id_column = $id";
    return $Link->query($update_sql);
}

$tables = getTables($Link);
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';
$id_column = '';
$records = [];
$row = [];

if ($table) {
    try {
        list($id_column, $records) = getRecords($Link, $table);
        if ($id) {
            $row = getRecordData($Link, $table, $id_column, $id);
        }
    } catch (Exception $e) {
        echo "<p>Произошла ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = $_POST['table'];
    $id = intval($_POST['id']);
    try {
        list($id_column, $records) = getRecords($Link, $table);
        if (updateRecord($Link, $table, $id_column, $id, $_POST)) {
            echo "<p>Данные успешно обновлены!</p>";
        } else {
            echo "<p>Ошибка обновления: " . $Link->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>Произошла ошибка: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование записей</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="addd.css">
</head>
<body>
    <form method="get">
        <label>Выберите таблицу:</label>
        <select name="table" onchange="this.form.submit()">
            <option value="">Выберите таблицу</option>
            <?php foreach ($tables as $t): ?>
                <option value="<?php echo $t; ?>" <?php if ($t == $table) echo 'selected'; ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($table): ?>
        <form method="get">
            <input type="hidden" name="table" value="<?php echo $table; ?>">
            <label>Выберите запись:</label>
            <select name="id" onchange="this.form.submit()">
                <option value="">Выберите ID</option>
                <?php foreach ($records as $record): ?>
                    <option value="<?php echo $record; ?>" <?php if ($record == $id) echo 'selected'; ?>><?php echo $record; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>

    <?php if ($id && $row): ?>
        <form method="post">
    <input type="hidden" name="table" value="<?php echo $table; ?>">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <?php foreach ($row as $key => $value): ?>
        <div class="form-group">
            <label><?php echo $key; ?>:</label>
            <input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
        </div>
    <?php endforeach; ?>
    <button type="submit">Сохранить изменения</button>
</form>
    <?php endif; ?>
</body>
</html>

