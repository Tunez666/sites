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
    $id_column = getIdColumn($table);
    
    $sql = "SELECT $id_column FROM $table ORDER BY $id_column";
    $result = $Link->query($sql);
    
    // Проверка успешности выполнения запроса
    if ($result === false) {
        echo "<p class='error-message'>Ошибка SQL: " . $Link->error . "</p>";
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
        echo "<p class='error-message'>Ошибка SQL: " . $Link->error . "</p>";
        return [];
    }
    
    return $result->fetch_assoc();
}

// Функция для удаления записи
function deleteRecord($Link, $table, $id_column, $id) {
    $sql = "DELETE FROM $table WHERE $id_column = ?";
    $stmt = $Link->prepare($sql);
    
    if ($stmt === false) {
        return "Ошибка подготовки запроса: " . $Link->error;
    }
    
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        return "Запись успешно удалена!";
    } else {
        return "Ошибка удаления записи: " . $stmt->error;
    }
}

// Функция для получения связанных данных (для отображения информативных имен)
function getRelatedData($Link, $table, $column, $value) {
    // Маппинг колонок к связанным таблицам и полям для отображения
    $relationMapping = [
        'id_sotr' => ['table' => 'sotrudniki', 'display' => 'fio'],
        'id_dol' => ['table' => 'doljnost', 'display' => 'name'],
        'id_dop' => ['table' => 'dop_u', 'display' => 'name'],
        'id_v' => ['table' => 'vyzovy', 'display' => 'telephone'],
        'id_t' => ['table' => 'tarifs', 'display' => 'name'],
        'id_m' => ['table' => 'marks', 'display' => 'name'],
        'id_a' => ['table' => 'auto', 'display' => 'reg_num'],
        'id_operatora' => ['table' => 'sotrudniki', 'display' => 'fio'],
        'id_shofera' => ['table' => 'sotrudniki', 'display' => 'fio'],
        'id_mechanika' => ['table' => 'sotrudniki', 'display' => 'fio']
    ];
    
    if (isset($relationMapping[$column])) {
        $relatedTable = $relationMapping[$column]['table'];
        $displayField = $relationMapping[$column]['display'];
        $idField = getIdColumn($relatedTable);
        
        $sql = "SELECT $displayField FROM $relatedTable WHERE $idField = ?";
        $stmt = $Link->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $value);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row[$displayField];
            }
        }
    }
    
    return $value;
}

$tables = getTables($Link);
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';
$id_column = '';
$records = [];
$row = [];
$message = '';
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if ($table) {
    try {
        list($id_column, $records) = getRecords($Link, $table);
        if ($id) {
            $row = getRecordData($Link, $table, $id_column, $id);
        }
    } catch (Exception $e) {
        $message = "<p class='error-message'>Произошла ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete']) && $_POST['delete'] === 'yes') {
    $table = $_POST['table'];
    $id = intval($_POST['id']);
    $id_column = $_POST['id_column'];
    
    try {
        $message = deleteRecord($Link, $table, $id_column, $id);
        // Сбрасываем ID после удаления
        $id = '';
        $row = [];
        // Обновляем список записей
        list($id_column, $records) = getRecords($Link, $table);
    } catch (Exception $e) {
        $message = "<p class='error-message'>Произошла ошибка: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Удаление записи</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="addd.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h2>Удаление записи</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'успешно') !== false ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="get">
            <div class="form-group">
                <label>Выберите таблицу:</label>
                <select name="table" onchange="this.form.submit()">
                    <option value="">Выберите таблицу</option>
                    <?php foreach ($tables as $t): ?>
                        <option value="<?php echo $t; ?>" <?php if ($t == $table) echo 'selected'; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($table): ?>
            <form method="get">
                <input type="hidden" name="table" value="<?php echo $table; ?>">
                <div class="form-group">
                    <label>Выберите запись:</label>
                    <select name="id" onchange="this.form.submit()">
                        <option value="">Выберите ID</option>
                        <?php foreach ($records as $record): ?>
                            <option value="<?php echo $record; ?>" <?php if ($record == $id) echo 'selected'; ?>><?php echo $record; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        
            
            <?php if (!$confirmed): ?>
                <div class="delete-warning">
                    Вы уверены, что хотите удалить эту запись? Это действие нельзя отменить.
                </div>
                <div class="button-container">
                    <a href="?table=<?php echo $table; ?>&id=<?php echo $id; ?>&confirm=yes" class="delete-button">Да, удалить</a>
                    <a href="?table=<?php echo $table; ?>" class="cancel-button">Отмена</a>
                </div>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="table" value="<?php echo $table; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="id_column" value="<?php echo $id_column; ?>">
                    <input type="hidden" name="delete" value="yes">
                    <div class="button-container">
                        <button type="submit" class="delete-button">Подтвердить удаление</button> <br> <br>
                        <a href="?table=<?php echo $table; ?>" class="cancel-button">Отмена</a>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>

