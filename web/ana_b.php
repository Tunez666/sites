<?php
require 'connect.php';

$sql = "SELECT b.name_b, COUNT(r.inv_num) AS read_count 
        FROM regist r 
        JOIN book b ON r.inv_num = b.inv_num 
        GROUP BY b.name_b 
        ORDER BY read_count DESC;";

$result = $Link->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Популярность книг</title>
</head>
<body>
    <h2>Аналитика популярности книг</h2>

    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>Название книги</th>
                <th>Количество выдач</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name_b']) ?></td>
                    <td><?= htmlspecialchars($row['read_count']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Данных о популярности книг пока нет.</p>
    <?php endif; ?>

    <br>
    <a href="index.php">Вернуться на главную</a>
</body>
</html>

<?php
$Link->close();
?>
