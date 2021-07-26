<?php
require('./inc_bot/class/autoloader.php'); // подключаем класс VIZ by on1x
require('./inc_bot/function.php'); // подключаем функции
$check_time=microtime();

global $apinode;
$apinode="https://viz.lexai.host/";
$prefix="VIZ"; // далее ноды виз

$timewaitdef=10*60; // включаем через 10 минут

$only="ALL"; // по умолчанию ручное управление для всех

if (isset($_GET['w']) && $_GET['w']<>"") { // ручное управление &w=jackvote - только для указанного делегата
    $only=$_GET['w'];
}

//================= witness setting begin =================
$witness="jackvote";
$wif='5Qwerty...';
$keyon="VIZ5Cs3hmjaHF5Mm744D9Ed56ikcNovYHAH4wBM15K9xuDphuxZAA";

$url="https://control.viz.world";
$reason="AutoDisableWeb";
$timewait=$timewaitdef; // устанавливаем дефолтное значение или прописываем свой таймаут

if  ( isset($_GET['m']) && ($only=="ALL" || $only==$witness) ) { // ручное отключение ?m=off / включение ?m=on
    updateManual($wif, $witness, $url, $keyon, $apinode, $_GET['m']);
}

if (file_exists($witness.".disable")==true) { // пока есть флаг ручного отключения - игнорируем проверку (и включение)
    echo "<br>Manual disable - remove flag to enable<br>\n";
} else {
    checkWitness($witness, $wif, $keyon, $url, $reason, $timewait, $prefix); // проверка на пропущенные блоки и [де]активация
}
//=================  witness setting end  =================

//================= witness setting begin =================
$witness="retroscope";
$wif='5Asdfgh...';
$keyon="VIZ5m14X9UrUkZUM67A546ak6CezBKce3TbYrMJQFXqGKDSmQNN9B";

$url="https://control.viz.world";
$reason="AutoDisableWeb";

$timewait=$timewaitdef; // устанавливаем дефолтное значение или прописываем свой таймаут

if  ( isset($_GET['m']) && ($only=="ALL" || $only==$witness) ) { // ручное отключение ?m=off / включение ?m=on
    updateManual($wif, $witness, $url, $keyon, $apinode, $_GET['m']);
}

if (file_exists($witness.".disable")==true) { // пока есть флаг ручного отключения - игнорируем проверку (и включение)
    echo "<br>Manual disable - remove flag to enable<br>\n";
} else {
    checkWitness($witness, $wif, $keyon, $url, $reason, $timewait, $prefix); // проверка на пропущенные блоки и [де]активация
}
//=================  witness setting end  =================


echo "<br>Ok. ".(microtime()-$check_time);
?>
