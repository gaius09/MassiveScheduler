<?php

$orderedQueue = array();
$key = 987654;
$ipc = msg_get_queue($key);
$msg = null;
$tipoMensaje = null;

echo "DAEMON: Ejecutando... \n";

while (true) {
    usleep(10000);
    while (msg_receive($ipc, 0, $tipoMensaje, 512 * 1024, $msg, TRUE, MSG_IPC_NOWAIT)) {
        echo "DAEMON: Mensaje recibido \n";
//        var_dump($message);

        if ($msg['action'] == 1) { //Add
            echo "==DAEMON: " . $msg['id'] . " TIME:" . date("H:i:s", $msg['time']) . " \n";

            array_push($orderedQueue, $msg);
        } elseif ($msg['action'] == 2) { //Edit
        } elseif ($msg['action'] == 3) { //Delete
            deleteMessageById($orderedQueue, $msg['id']);
        }

        sort($orderedQueue);

//      var_dump($orderedQueue);
    }
    executeMessage($orderedQueue);
}

function deleteMessageById(&$colaOrdenada, $id) {
    for ($index = 0; $index < count($colaOrdenada); $index++) {
        $qMsg = $colaOrdenada[$index];
        if ($qMsg['id'] == $id) {
            unset($colaOrdenada[$index]);
            array_values($colaOrdenada);
            echo "DAEMON: Mesnsaje borrado: $id \n";
            //executeTask($id);
            break;
        }
    }
}

function executeMessage(&$orderedQueue) {
    $msg = isset($orderedQueue[0]) ? $orderedQueue[0] : null;
    if ($msg) {
        $taskTime = $msg['time'];
        $now = microtime(true);
        if ($taskTime <= $now) {
            $id = $msg['id'];
            echo "DAEMON: Ejecutando: $id \n";
            array_shift($orderedQueue);
            array_values($orderedQueue);
            executeTask($id);
        }
    } else {
        //echo "No existe " . '$colaOrdenada[0]'. " al ejecutar mensaje \n";
    }
}

function executeTask($id) {
    echo "DAEMON: Mando petición...\n";
    $urlTask = 'http://localhost/MassiveScheduler/public_files/task.php';
    $ch = curl_init($urlTask . '?id=' . $id);
    curl_exec($ch);
    curl_close($ch);
    echo "DAEMON: cURL cerrado \n";
}

?>