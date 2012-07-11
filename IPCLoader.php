<?php

require_once 'Task.php';

$key = 987654;
$ipc = msg_get_queue($key);
$tipoMensaje = 1;
for ($i = 0; $i < 200; $i++) {
    $tarea = new Task(microtime(true) + mt_rand(3, 20), "Tarea $i");
    msg_send($ipc, $tipoMensaje, $tarea);
}

?>