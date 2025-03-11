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

// Функция для получения структуры таблицы
function getTableStructure($Link, $table) {
    $structure = [];
    $sql = "DESCRIBE $table";
    $result = $Link->query($sql);
    
    if ($result === false) {
        echo "<p>Ошибка SQL: " . $Link->error . "</p>";
        return [];
    }
    
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    return $structure;
}

// Функция для получения связанных данных для выпадающих списков
function getRelatedData($Link, $table, $column) {
    $relatedData = [];
    
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
        
        $sql = "SELECT $idField, $displayField FROM $relatedTable";
        $result = $Link->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $relatedData[] = [
                    'id' => $row[$idField],
                    'name' => $row[$displayField]
                ];
            }
        }
    }
    
    return $relatedData;
}

// Функция для добавления новой записи
function addRecord($Link, $table, $data) {
    $columns = [];
    $placeholders = [];
    $values = [];
    $types = '';
    
    foreach ($data as $column => $value) {
        if ($column !== 'table') {
            $columns[] = $column;
            $placeholders[] = '?';
            $values[] = $value;
            
            // Определяем тип данных для bind_param
            if (is_int($value)) {
                $types .= 'i'; // integer
            } elseif (is_float($value)) {
                $types .= 'd'; // double
            } else {
                $types .= 's'; // string
            }
        }
    }
    
    $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $Link->prepare($sql);
    
    if ($stmt === false) {
        return "Ошибка подготовки запроса: " . $Link->error;
    }
    
    // Динамически привязываем параметры
    $bindParams = array($types);
    for ($i = 0; $i < count($values); $i++) {
        $bindParams[] = &$values[$i];
    }
    
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    
    if ($stmt->execute()) {
        return "Запись успешно добавлена!";
    } else {
        return "Ошибка добавления записи: " . $stmt->error;
    }
}

$tables = getTables($Link);
$table = $_GET['table'] ?? '';
$structure = [];
$message = '';

if ($table) {
    $structure = getTableStructure($Link, $table);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table = $_POST['table'];
    unset($_POST['table']); // Удаляем таблицу из данных для вставки
    
    // Преобразуем строковые числа в целые числа для числовых полей
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'id_') === 0 && is_numeric($value)) {
            $_POST[$key] = intval($value);
        }
    }
    
    $message = addRecord($Link, $table, $_POST);
    
    // Восстанавливаем структуру таблицы после добавления
    $structure = getTableStructure($Link, $table);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление записи</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="addd.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h2>Добавление новой записи</h2>
        
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

        <?php if ($table && !empty($structure)): ?>
            <form method="post">
                <input type="hidden" name="table" value="<?php echo $table; ?>">
                
                <?php foreach ($structure as $column): ?>
                    <?php 
                    // Пропускаем автоинкрементные поля
                    if ($column['Extra'] == 'auto_increment') continue;
                    
                    $columnName = $column['Field'];
                    $columnType = $column['Type'];
                    $isRequired = $column['Null'] === 'NO' ? 'required' : '';
                    ?>
                    
                    <div class="form-group">
                        <label for="<?php echo $columnName; ?>"><?php echo $columnName; ?>:</label>
                        
                        <?php if (strpos($columnName, 'id_') === 0 && $columnName !== getIdColumn($table)): ?>
                            <?php $relatedData = getRelatedData($Link, $table, $columnName); ?>
                            
                            <?php if (!empty($relatedData)): ?>
                                <select id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" <?php echo $isRequired; ?>>
                                    <option value="">Выберите значение</option>
                                    <?php foreach ($relatedData as $item): ?>
                                        <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="number" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" <?php echo $isRequired; ?>>
                            <?php endif; ?>
                            
                        <?php elseif (strpos($columnType, 'int') !== false): ?>
                            <input type="number" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" <?php echo $isRequired; ?>>
                            
                        <?php elseif (strpos($columnType, 'date') !== false): ?>
                            <input type="date" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" value="<?php echo date('Y-m-d'); ?>" <?php echo $isRequired; ?>>
                            
                        <?php elseif (strpos($columnType, 'time') !== false): ?>
                            <input type="time" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" value="<?php echo date('H:i'); ?>" <?php echo $isRequired; ?>>
                            
                        <?php elseif ($columnName == 'telephone'): ?>
                            <input type="tel" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" placeholder="+7 (___) ___-__-__" <?php echo $isRequired; ?> class="phone-input">
                            
                        <?php elseif (strpos($columnType, 'text') !== false): ?>
                            <textarea id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" rows="4" <?php echo $isRequired; ?>></textarea>
                            
                        <?php else: ?>
                            <input type="text" id="<?php echo $columnName; ?>" name="<?php echo $columnName; ?>" <?php echo $isRequired; ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="button-container">
                    <button type="submit">Добавить запись</button>
                </div>
            </form>
        <?php elseif ($table): ?>
            <p>Не удалось получить структуру таблицы.</p>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
    
    <script>
        // Маска для телефона
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInputs = document.querySelectorAll('.phone-input');
            
            phoneInputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                    e.target.value = !x[2] ? x[1] : '+' + x[1] + ' (' + x[2] + ') ' + (x[3] ? x[3] + '-' : '') + (x[4] ? x[4] + '-' : '') + x[5];
                });
            });
        });
    </script>
</body>
</html>

