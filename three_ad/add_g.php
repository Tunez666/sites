<?php
require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query = "INSERT INTO `guests`(`surname`, `name`, `lastname`, `date_b`, `seria_p`, `num_p`, 
        `kem_vyd`, `address`, `num_t`, `srok_proz`, `id_apart`, `pribyl`, `vybyl`, `num_auto`, `prodlenie`, `id_dop`, `id_emp`, `summ`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
    
    $stmt = mysqli_prepare($Link, $query);
    mysqli_stmt_bind_param($stmt, "sssssisississsssidi", $_POST['surname'], $_POST['name'], $_POST['lastname'], 
        $_POST['date_b'], $_POST['seria_p'], $_POST['num_p'], $_POST['kem_vyd'], $_POST['address'], $_POST['num_t'], 
        $_POST['srok_proz'], $_POST['id_apart'], $_POST['pribyl'], $_POST['vybyl'], $_POST['num_auto'], $_POST['prodlenie'], 
        $_POST['id_dop'], $_POST['id_emp'], $_POST['summ']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: index.php");
    exit();
}

function getOptions($table, $id, $name) {
    global $Link;
    $result = mysqli_query($Link, "SELECT $id, $name FROM $table");
    $options = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $options .= "<option value='" . $row[$id] . "'>" . $row[$name] . "</option>";
    }
    return $options;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить гостя</title>
    <link rel="stylesheet" href="ad.css">
</head>
<body>
    <div class="container">
        <h2>Добавить гостя</h2>
        <form action="" method="POST" class="form-container">
            <div class="form-column">
                <label>Фамилия</label> <input type="text" name="surname" required>
                <label>Имя</label> <input type="text" name="name" required>
                <label>Отчество</label> <input type="text" name="lastname">
                <label>Дата рождения</label> <input type="date" name="date_b" required>
                <label>Серия паспорта</label> <input type="number" name="seria_p" required>
                <label>Номер паспорта</label> <input type="number" name="num_p" required>
                <label>Кем выдан</label> <input type="text" name="kem_vyd" required>
                <label>Адрес проживания</label> <input type="text" name="address" required>
                <label>Номер телефона</label> <input type="number" name="num_t" required>
            </div>
            <div class="form-column">
                <label>Срок проживания</label> <input type="number" name="srok_proz" required>
                <label>Номер апартаментов</label> <select name="id_apart"><?= getOptions('apartments', 'id_apart', 'number') ?></select>
                <label>Прибыл</label> <input type="date" name="pribyl" required>
                <label>Выбыл</label> <input type="date" name="vybyl">
                <label>Номер машины</label> <input type="text" name="num_auto">
                <label>Продление</label> <input type="text" name="prodlenie">
                <label>Доп услуги</label> <select name="id_dop"><?= getOptions('services', 'id_dop', 'service_name') ?></select>
                <label>Кто заселил</label> <select name="id_emp"><?= getOptions('employees', 'id_emp', 'name') ?></select>
                <label>Сумма</label> <input type="number" name="summ" required>
            </div>
            <div class="form-submit">
                <input type="submit" value="Добавить">
            </div>
        </form>
    </div>
</body>
</html>
