<?php
require_once "connect.php";

// Функция для получения данных отдела кадров (сотрудники и их должности)
function getHRData($Link) {
    $data = [];
    
    $sql = "SELECT s.id_sotr, s.fio, s.vozrast, s.pol, s.adres, s.pasport_data, 
                   d.id_dol, d.name as doljnost_name, d.oklad, d.obyzanosti, d.trebovania 
            FROM sotrudniki s
            JOIN doljnost d ON s.id_dol = d.id_dol
            ORDER BY s.fio";
            
    $result = $Link->query($sql);
    
    if ($result === false) {
        echo "<p class='error-message'>Ошибка SQL: " . $Link->error . "</p>";
        return [];
    }
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Получаем данные
$hrData = getHRData($Link);

// Фильтрация по должности
$positions = [];
$selectedPosition = $_GET['position'] ?? '';

// Получаем список всех должностей для фильтра
$positionQuery = "SELECT id_dol, name FROM doljnost ORDER BY name";
$positionResult = $Link->query($positionQuery);
if ($positionResult) {
    while ($row = $positionResult->fetch_assoc()) {
        $positions[$row['id_dol']] = $row['name'];
    }
}

// Применяем фильтр, если выбрана должность
$filteredData = $hrData;
if (!empty($selectedPosition)) {
    $filteredData = array_filter($hrData, function($item) use ($selectedPosition) {
        return $item['id_dol'] == $selectedPosition;
    });
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отдел кадров</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="kadr.css">
    <style>
       
    </style>
</head>
<body>
    <div class="container">
        <h2>Отдел кадров</h2>
        
        <div class="filter-container">
            <form method="get" class="form-group">
                <label for="position">Фильтр по должности:</label>
                <select name="position" id="position" onchange="this.form.submit()">
                    <option value="">Все должности</option>
                    <?php foreach ($positions as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php if ($id == $selectedPosition) echo 'selected'; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <?php if (count($filteredData) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Сотрудник</th>
                        <th>Должность</th>
                        <th>Оклад</th>
                        <th>Обязанности</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredData as $employee): ?>
                        <tr>
                            <td>
                                <div class="employee-details">
                                    <span class="employee-name"><?php echo $employee['fio']; ?></span>
                                    <span class="employee-info">Возраст: <?php echo $employee['vozrast']; ?></span>
                                    <span class="employee-info">Пол: <?php echo $employee['pol']; ?></span>
                                    <span class="employee-info">Адрес: <?php echo $employee['adres']; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="position-details">
                                    <span class="position-name"><?php echo $employee['doljnost_name']; ?></span>
                                </div>
                            </td>
                            <td class="salary"><?php echo number_format($employee['oklad'], 0, ',', ' '); ?> руб.</td>
                            <td><?php echo $employee['obyzanosti']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">Нет данных для отображения</div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">Вернуться на главную</a>
    </div>
</body>
</html>

