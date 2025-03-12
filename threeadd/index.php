<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <!-- Добавляем Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="a.css">
</head>
<body>
    <div class="container">
    <img src="foto.jpg" width = "500px" hight = "300px"> 
        <h1>Главная</h1>
        
        <div class="button-container">
            <button class="btn-new" onclick="location.href='table.php'">Посмотреть</button>
            <button class="btn-new" onclick="location.href='add.php'">Добавить</button>
            <button class="btn-new" onclick="location.href='update.php'">Изменить</button>
            <button class="btn-new" onclick="location.href='delete.php'">Удалить</button>
            
        </div>
        <br>
            <button class="btn-new" onclick="location.href='kadry.php'">Запрос отдел кадров</button>
            <button class="btn-new" onclick="location.href='autopark.php'">Запрос автопарк</button>
            <button class="btn-new" onclick="location.href='vyzoz.php'">Запрос на список вызовов</button>
    </div>
</body>
</html>

