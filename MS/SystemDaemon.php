#!/usr/bin/php -q
<?php
date_default_timezone_set('Europe/Madrid');

require_once "System/Daemon.php";

// Bare minimum setup
System_Daemon::setOption("appName", "super_hero");
System_Daemon::setOption("authorEmail", "micorreo08.0@gmail.com");
System_Daemon::setOption("appDescription", "Demonio del juego");
System_Daemon::setOption("authorName", "Andres");

//System_Daemon::setOption("appDir", dirname(__FILE__));
System_Daemon::log(System_Daemon::LOG_INFO, "Daemon not yet started so " .
        "this will be written on-screen");

// Spawn Deamon!
System_Daemon::writeAutoRun();
System_Daemon::start();
System_Daemon::log(System_Daemon::LOG_INFO, "Daemon: '" .
        System_Daemon::getOption("appName") .
        "' spawned! This will be written to " .
        System_Daemon::getOption("logLocation"));

// Your normal PHP code goes here. Only the code will run in the background
// so you can close your terminal session, and the application will
// still run.


require_once 'Task.php';

$orderedQueue = array();
$key = 987654;
$ipc = msg_get_queue($key);
$msg = null;
$tipoMensaje = null;

while (true) {
    usleep(10000);
    while (msg_receive($ipc, 0, $tipoMensaje, 512 * 1024, $msg, TRUE, MSG_IPC_NOWAIT)) {
        array_push($orderedQueue, $msg);
        sort($orderedQueue);
    }
    executeMessage($orderedQueue);
}

function executeMessage(&$colaOrdenada) {
    $msg = new Task(null, null);
    $msg = isset($colaOrdenada[0]) ? $colaOrdenada[0] : null;
    if ($msg) {
        $taskTime = $msg->getHoraEjecucion();
        $tiempoActual = microtime(true);
        if ($taskTime <= $tiempoActual) {
            $mensaje = $msg->getMensaje();
            System_Daemon::log(System_Daemon::LOG_INFO, "Mensaje: $mensaje");
//            echo "Mensaje: $mensaje \n";
            array_shift($colaOrdenada);
            array_values($colaOrdenada);
        }
    }
}

//System_Daemon::stop();
?>