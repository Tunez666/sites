<?php
require_once "connect.php"; 
$tableName = 'employees';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($Link, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
       
        $query = "SELECT * FROM `$tableName` WHERE username = ?";
        $stmt = mysqli_prepare($Link, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            
            if ($password === $row['pass']) {
                echo "<p>Добро пожаловать, $username!</p>";
                header("Location: index.php");
                exit();
            } else {
                echo "<p>Неверный логин или пароль.</p>";
            }
        } else {
            echo "<p>Пользователь не найден.</p>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>Пожалуйста, введите имя пользователя и пароль.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="au.css">
</head>
<body>
    <h2>Добро пожаловать!</h2>
    <center>
        <form action="" method="POST">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required><br>
            <input type="submit" value="Войти">
        </form>
    </center>
</body>
</html>
