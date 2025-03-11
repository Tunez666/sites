<?php
require_once "connect.php";

// Функция для получения данных о вызовах
function getCallsData($Link) {
    $data = [];
    
    $sql = "SELECT v.id_v, v.date_v, v.time_v, v.telephone, v.otkuda, v.kuda,
                   t.id_t, t.name as tarif_name, t.stoimost as tarif_stoimost,
                   d.id_dop, d.name as usluga_name, d.stoimost as usluga_stoimost,
                   a.id_a, a.reg_num, m.name as marka_name,
                   s.id_sotr, s.fio as operator_name
            FROM vyzovy v
            JOIN tarifs t ON v.id_t = t.id_t
            LEFT JOIN dop_u d ON v.id_dop = d.id_dop
            JOIN auto a ON v.id_a = a.id_a
            JOIN marks m ON a.id_m = m.id_m
            JOIN sotrudniki s ON v.id_operatora = s.id_sotr
            ORDER BY v.date_v DESC, v.time_v DESC";
            
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
$callsData = getCallsData($Link);

// Фильтрация по дате
$dates = [];
$selectedDate = $_GET['date'] ?? '';

// Получаем список всех дат для фильтра
foreach ($callsData as $call) {
    $dates[$call['date_v']] = $call['date_v'];
}
// Сортируем даты в обратном порядке (новые сначала)
krsort($dates);

// Применяем фильтр, если выбрана дата
$filteredData = $callsData;
if (!empty($selectedDate)) {
    $filteredData = array_filter($callsData, function($item) use ($selectedDate) {
        return $item['date_v'] == $selectedDate;
    });
}

// Фильтрация по тарифу
$tariffs = [];
$selectedTariff = $_GET['tariff'] ?? '';

// Получаем список всех тарифов для фильтра
$tariffQuery = "SELECT id_t, name FROM tarifs ORDER BY name";
$tariffResult = $Link->query($tariffQuery);
if ($tariffResult) {
    while ($row = $tariffResult->fetch_assoc()) {
        $tariffs[$row['id_t']] = $row['name'];
    }
}

// Применяем фильтр по тарифу, если выбран
if (!empty($selectedTariff)) {
    $filteredData = array_filter($filteredData, function($item) use ($selectedTariff) {
        return $item['id_t'] == $selectedTariff;
    });
}

// Фильтрация по оператору
$operators = [];
$selectedOperator = $_GET['operator'] ?? '';

// Получаем список всех операторов для фильтра
$operatorQuery = "SELECT DISTINCT s.id_sotr, s.fio 
                  FROM sotrudniki s 
                  JOIN vyzovy v ON s.id_sotr = v.id_operatora 
                  ORDER BY s.fio";
$operatorResult = $Link->query($operatorQuery);
if ($operatorResult) {
    while ($row = $operatorResult->fetch_assoc()) {
        $operators[$row['id_sotr']] = $row['fio'];
    }
}

// Применяем фильтр по оператору, если выбран
if (!empty($selectedOperator)) {
    $filteredData = array_filter($filteredData, function($item) use ($selectedOperator) {
        return $item['id_sotr'] == $selectedOperator;
    });
}

// Форматирование телефонного номера
function formatPhone($phone) {
    // Очищаем от всех символов кроме цифр
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Если длина номера подходит для российского формата
    if (strlen($phone) === 11) {
        return '+' . substr($phone, 0, 1) . ' (' . substr($phone, 1, 3) . ') ' . 
               substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
    }
    
    // Если формат не определен, возвращаем как есть
    return $phone;
}

// Форматирование даты
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d.m.Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список вызовов</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vyz.css">
    <style>
       
    </style>
</head>
<body>
    <div class="container">
        <h2>Список вызовов</h2>
        
        <div class="filter-container">
            <div class="filter-item">
                <form method="get" class="form-group">
                    <input type="hidden" name="tariff" value="<?php echo $selectedTariff; ?>">
                    <input type="hidden" name="operator" value="<?php echo $selectedOperator; ?>">
                    <label for="date">Фильтр по дате:</label>
                    <select name="date" id="date" onchange="this.form.submit()">
                        <option value="">Все даты</option>
                        <?php foreach ($dates as $date): ?>
                            <option value="<?php echo $date; ?>" <?php if ($date == $selectedDate) echo 'selected'; ?>>
                                <?php echo formatDate($date); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="filter-item">
                <form method="get" class="form-group">
                    <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                    <input type="hidden" name="operator" value="<?php echo $selectedOperator; ?>">
                    <label for="tariff">Фильтр по тарифу:</label>
                    <select name="tariff" id="tariff" onchange="this.form.submit()">
                        <option value="">Все тарифы</option>
                        <?php foreach ($tariffs as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($id == $selectedTariff) echo 'selected'; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="filter-item">
                <form method="get" class="form-group">
                    <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                    <input type="hidden" name="tariff" value="<?php echo $selectedTariff; ?>">
                    <label for="operator">Фильтр по оператору:</label>
                    <select name="operator" id="operator" onchange="this.form.submit()">
                        <option value="">Все операторы</option>
                        <?php foreach ($operators as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($id == $selectedOperator) echo 'selected'; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        
        <?php if (count($filteredData) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Информация о вызове</th>
                        <th>Маршрут</th>
                        <th>Тариф и услуги</th>
                        <th>Автомобиль</th>
                        <th>Оператор</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredData as $call): ?>
                        <tr>
                            <td>
                                <div class="call-details">
                                    <span class="call-date">
                                        <?php echo formatDate($call['date_v']); ?> в <?php echo $call['time_v']; ?>
                                    </span>
                                    <span class="call-info">
                                        Телефон: <?php echo formatPhone($call['telephone']); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="route-details">
                                    <span class="route-title">Маршрут:</span>
                                    <span class="route-info">
                                        <strong>Откуда:</strong> <?php echo $call['otkuda']; ?>
                                    </span>
                                    <span class="route-info">
                                        <strong>Куда:</strong> <?php echo $call['kuda']; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="tariff-details">
                                    <span class="tariff-name"><?php echo $call['tarif_name']; ?></span>
                                    <span class="tariff-info price">
                                        <?php echo number_format($call['tarif_stoimost'], 0, ',', ' '); ?> руб.
                                    </span>
                                    <?php if (!empty($call['usluga_name'])): ?>
                                        <span class="tariff-info">
                                            Доп. услуга: <?php echo $call['usluga_name']; ?>
                                            (<?php echo number_format($call['usluga_stoimost'], 0, ',', ' '); ?> руб.)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="car-details">
                                    <span class="car-name"><?php echo $call['marka_name']; ?></span>
                                    <span class="car-info">
                                        Рег. номер: <?php echo $call['reg_num']; ?>
                                    </span>
                                </div>
                            </td>
                            <td class="operator-name"><?php echo $call['operator_name']; ?></td>
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

