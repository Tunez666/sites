<?php
$Link=mysqli_connect('127.0.0.1', 'root', '', 'taxopark');
if(!$Link){
    echo 'Ошибка подключения к бд. Код ошибки: '. mysqli_connect_error(). 'ошибка: '. mysqli_connect_error();
    exit;
}
?>