<?php
require 'connect.php';

$sql = "SELECT book.name_b AS 'Название книги', 
               book.author AS 'Автор', 
               reader.fio AS 'Читатель', 
               reader.adres AS 'Адрес', 
               regist.plan_dta_vozvr AS 'Плановая дата возврата' 
        FROM regist 
        JOIN book ON regist.inv_num = book.inv_num 
        JOIN reader ON regist.bill_num = reader.bill_num 
        WHERE regist.fact_vozvr = '0000-00-00' 
        ORDER BY regist.date_vyd DESC;";

$result = $Link->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Книги на руках</title>
</head>
<body>
    <h2>Книги на руках</h2>

    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>Название книги</th>
                <th>Автор</th>
                <th>Читатель</th>
                <th>Адрес</th>
                <th>Плановая дата возврата</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Название книги']) ?></td>
                    <td><?= htmlspecialchars($row['Автор']) ?></td>
                    <td><?= htmlspecialchars($row['Читатель']) ?></td>
                    <td><?= htmlspecialchars($row['Адрес']) ?></td>
                    <td><?= htmlspecialchars($row['Плановая дата возврата']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Нет книг на руках.</p>
    <?php endif; ?>

    <br>
    <a href="index.php">Вернуться на главную</a>
</body>
</html>

<?php
$Link->close();
?>
