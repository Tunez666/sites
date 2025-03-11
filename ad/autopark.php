<?php
require_once "connect.php";

// Функция для получения данных автопарка
function getFleetData($Link) {
    $data = [];
    
    $sql = "SELECT a.id_a, a.reg_num, a.kuz_num, a.dvig_num, a.god_vyp, a.probeg, 
                   m.id_m, m.name as marka_name, m.tx, m.stoimost, m.specifica,
                   s.id_sotr, s.fio as driver_name
            FROM auto a
            JOIN marks m ON a.id_m = m.id_m
            LEFT JOIN sotrudniki s ON a.id_shofera = s.id_sotr
            ORDER BY a.id_a";
            
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
$fleetData = getFleetData($Link);

// Фильтрация по марке автомобиля
$brands = [];
$selectedBrand = $_GET['brand'] ?? '';

// Получаем список всех марок для фильтра
$brandQuery = "SELECT id_m, name FROM marks ORDER BY name";
$brandResult = $Link->query($brandQuery);
if ($brandResult) {
    while ($row = $brandResult->fetch_assoc()) {
        $brands[$row['id_m']] = $row['name'];
    }
}

// Применяем фильтр, если выбрана марка
$filteredData = $fleetData;
if (!empty($selectedBrand)) {
    $filteredData = array_filter($fleetData, function($item) use ($selectedBrand) {
        return $item['id_m'] == $selectedBrand;
    });
}

// Фильтрация по водителю
$drivers = [];
$selectedDriver = $_GET['driver'] ?? '';

// Получаем список всех водителей для фильтра
$driverQuery = "SELECT DISTINCT s.id_sotr, s.fio 
                FROM sotrudniki s 
                JOIN auto a ON s.id_sotr = a.id_shofera 
                ORDER BY s.fio";
$driverResult = $Link->query($driverQuery);
if ($driverResult) {
    while ($row = $driverResult->fetch_assoc()) {
        $drivers[$row['id_sotr']] = $row['fio'];
    }
}

// Применяем фильтр по водителю, если выбран
if (!empty($selectedDriver)) {
    $filteredData = array_filter($filteredData, function($item) use ($selectedDriver) {
        return $item['id_sotr'] == $selectedDriver;
    });
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Автопарк</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="autop.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <h2>Автопарк</h2>
        
        <div class="filter-container">
            <div class="filter-item">
                <form method="get" class="form-group">
                    <input type="hidden" name="driver" value="<?php echo $selectedDriver; ?>">
                    <label for="brand">Фильтр по марке:</label>
                    <select name="brand" id="brand" onchange="this.form.submit()">
                        <option value="">Все марки</option>
                        <?php foreach ($brands as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($id == $selectedBrand) echo 'selected'; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="filter-item">
                <form method="get" class="form-group">
                    <input type="hidden" name="brand" value="<?php echo $selectedBrand; ?>">
                    <label for="driver">Фильтр по водителю:</label>
                    <select name="driver" id="driver" onchange="this.form.submit()">
                        <option value="">Все водители</option>
                        <?php foreach ($drivers as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($id == $selectedDriver) echo 'selected'; ?>>
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
                        <th>Автомобиль</th>
                        <th>Марка</th>
                        <th>Стоимость</th>
                        <th>Водитель</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredData as $car): ?>
                        <tr>
                            <td>
                                <div class="car-details">
                                    <span class="car-name">Рег. номер: <?php echo $car['reg_num']; ?></span>
                                    <span class="car-info">Кузов: <?php echo $car['kuz_num']; ?></span>
                                    <span class="car-info">Двигатель: <?php echo $car['dvig_num']; ?></span>
                                    <span class="car-info">Год выпуска: <?php echo $car['god_vyp']; ?></span>
                                    <span class="car-info">Пробег: <?php echo number_format($car['probeg'], 0, ',', ' '); ?> км</span>
                                </div>
                            </td>
                            <td>
                                <div class="brand-details">
                                    <span class="brand-name"><?php echo $car['marka_name']; ?></span>
                                    <span class="brand-info">Тип: <?php echo $car['tx']; ?></span>
                                    <span class="brand-info">Спецификация: <?php echo $car['specifica']; ?></span>
                                </div>
                            </td>
                            <td class="price"><?php echo number_format($car['stoimost'], 0, ',', ' '); ?> руб.</td>
                            <td class="driver-name"><?php echo $car['driver_name'] ?: 'Не назначен'; ?></td>
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

