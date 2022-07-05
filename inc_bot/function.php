<?php

function checkWitness($witness, $wif, $keyon, $url, $reason, $timewait, $prefix, $mail) {
    //file_put_contents("log/log.txt", date("d-m-Y H:i:s", time())."|".$prefix.$witness."\n", FILE_APPEND | LOCK_EX);
    global $apinode;
    $keyoff=$prefix."1111111111111111111111111111111114T1Anm";

    $line=file_get_contents("acc/".$witness."_".strtolower($prefix).".last"); // GET
    list($timeold, $count)=explode("|", $line); // извлекаем последнее число пропущенных блоков

    $obj=getWitness($witness, $apinode);

    $log=date("d-m-Y H:i:s", time())."|".$obj['total_missed']."|".$witness."|".$obj['last_confirmed_block_num']."\n";
    $file="log/".$witness.".".strtolower($prefix);

    if ($obj['total_missed']>$count) { // если количество пропущенных блоков увеличилось с момента последней проверки
        file_put_contents("acc/".$witness."_".strtolower($prefix).".last", time()."|".$obj['total_missed']); // записывем пропущенные блоки
        if ($obj['signing_key']<>$keyoff) {
            $result=updateWitness($wif, $witness, $reason, $keyoff, $apinode); // disable
            file_put_contents($file, "D|".$log, FILE_APPEND | LOCK_EX); // пишем лог
            echo "<br>Disable";
            if ($mail<>"") { // если не пустой емайл - отправляем сообщение
                sendMail($mail, "Отключение ноды", "Пропущено блоков: ".($obj['total_missed']-$count)."\n".$log );
            }
        } else {
            file_put_contents($file, "N|".$log, FILE_APPEND | LOCK_EX); // пишем лог
            echo "<br>Now disabled<br>";
        }
    } else {
        if ( $obj['signing_key']==$keyoff ) {
            if ( (time()-$timeold)>$timewait ) {
                $result=updateWitness($wif, $witness, $url, $keyon, $apinode); // enable
                file_put_contents($file, "E|".$log, FILE_APPEND | LOCK_EX); // пишем лог
                echo "Enable<br>";
                if ($mail<>"") { // если не пустой емайл - отправляем сообщение
                    sendMail($mail, "Включение ноды", "Всего пропущено: ".$obj['total_missed']."\n".$log );
                }
            } else {                                                        // wait
                file_put_contents($file, "W|".$log, FILE_APPEND | LOCK_EX); // пишем лог
                echo "<br>Wait<br>";
            }
        } else {
            echo "<br>All right ".$witness."<br>";                                           // no problem
//            file_put_contents($file, "S|".$log, FILE_APPEND | LOCK_EX); // пишем лог
        }
    }
} // checkWitness

function getWitness($whois, $apinode) {
    $api=new VIZ\JsonRPC($apinode);
    $account=$api->execute_method('get_witness_by_account',[$whois]);
    return $account;
} // getWitness

function updateWitness($wif, $whois, $url, $key, $apinode) {
// Запись ключа делегата
//    global $apinode;
    $tx=new VIZ\Transaction($apinode,$wif);
    $tx_data=$tx->witness_update($whois, $url, $key);
    $tx->api->return_only_result=false;
    $tx_status=$tx->execute($tx_data['json']);
    var_dump($tx_status);
    return $tx_status;
} // updateWitness

function updateManual($wif, $whois, $url, $keyon, $apinode, $manual) {
    $ip = $_SERVER['REMOTE_ADDR'];
    if  ($manual=='on') { // ручное включение
        updateWitness($wif, $whois, $url, $keyon, $apinode);
        unlink("log/".$whois.".last"); // удаляем. Через минуту создастся с актуальным содержимым
        unlink($whois.".disable"); // удаляем флаг ручного отключения
        echo "<br>Manual enable ".$whois;
        file_put_contents("log/log.txt", date("d-m-Y H:i:s", time())."|".$ip."|Enable|".$whois."\n", FILE_APPEND | LOCK_EX);
    }

    if  ($manual=='off') { // ручное отключение
        $keyoff="VIZ1111111111111111111111111111111114T1Anm";
        updateWitness($wif, $whois, "Manual disable", $keyoff, $apinode);
        echo "<br>Manual disable ".$whois;
        file_put_contents($whois.".disable", date("d-m-Y H:i:s", time())."|".$ip); // создаём флаг для игнорирования автоматического включения
        file_put_contents("log/log.txt", date("d-m-Y H:i:s", time())."|".$ip."|Disable|".$whois."\n", FILE_APPEND | LOCK_EX);
    }

} // updateManual

function sendMail($to, $subject, $message) {
    $headers = 'From: checkwitness@' . $_SERVER[HTTP_HOST] . "\r\n" .
    'Reply-To: devnull@' . $_SERVER[HTTP_HOST] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
     
    mail($to, $subject, $message, $headers);
}

?>
