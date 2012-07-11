<?php

require_once 'Task.php';

$colaOrdenada = array();
$key = 987654;
$ipc = msg_get_queue($key);
$mensaje = null;
$tipoMensaje = null;

while (true) {
    usleep(10000);
    while (msg_receive($ipc, 0, $tipoMensaje, 512 * 1024, $mensaje, TRUE, MSG_IPC_NOWAIT)) {
        array_push($colaOrdenada, $mensaje);
        sort($colaOrdenada);
    }
    executeMessage($colaOrdenada);
}

function executeMessage(&$colaOrdenada) {
    $msg = new Task(null, null);
    $msg = isset($colaOrdenada[0]) ? $colaOrdenada[0] : null;
    if ($msg) {
        $taskTime = $msg->getHoraEjecucion();
        $tiempoActual = microtime(true);
        if ($taskTime <= $tiempoActual) {
            $mensaje = $msg->getMensaje();
            echo "Mensaje: $mensaje \n";
            array_shift($colaOrdenada);
            array_values($colaOrdenada);
        }
    }
}

?>