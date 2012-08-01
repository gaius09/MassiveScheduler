<?php

namespace Daemon;

require_once __DIR__ . '/../Loaders/loader.php';

use MS\MongoDataManager;
use MS\Documents\ATask;

class IPCDaemon {

    const ACTION_ADD = 1;
    const ACTION_EDIT = 2;
    const ACTION_DELETE = 3;

    private $orderedQueue;
    private $key;
    private $ipc;

    public function __construct() {
        $this->orderedQueue = array();
        $this->key = 987654;
        $this->ipc = msg_get_queue($this->key);
        $this->init();
    }

    public function init() {
        echo "DAEMON: Ejecutando... \n";

        $message = null;
        $messageType = null;

        while (true) {
            usleep(10000);
            while (msg_receive($this->ipc, 0, $messageType, 512 * 1024, $message, TRUE, MSG_IPC_NOWAIT)) {
//                echo "DAEMON: Mensaje recibido \n";
                //        var_dump($message);

                if ($message['action'] == self::ACTION_ADD) {
                    echo "Tarea con id " . $message['id'] . " se tiene que ejecutar a las :" . date("H:i:s", $message['time']) . " \n";
                    array_push($this->orderedQueue, $message);
                } elseif ($message['action'] == self::ACTION_EDIT) {
                    
                } elseif ($message['action'] == self::ACTION_DELETE) {
                    $this->deleteMessageById($message['id']);
                }
                sort($this->orderedQueue);

//      var_dump($orderedQueue);
            }
            $this->executeMessage();
        }
    }

    private function deleteMessageById($id) {
        $orderedQueueLength = count($this->orderedQueue);
        for ($index = 0; $index < $orderedQueueLength; $index++) {
            $qMsg = $this->orderedQueue[$index];
            if ($qMsg['id'] == $id) {
                unset($this->orderedQueue[$index]);
                array_values($this->orderedQueue);
                echo "Tarea con id $id ha sido borrada \n";
                //executeTask($id);
                break;
            }
        }
    }

    private function executeMessage() {
        $msg = isset($this->orderedQueue[0]) ? $this->orderedQueue[0] : null;
        if ($msg) {
            $taskTime = $msg['time'];
            $now = microtime(true);
            if ($taskTime <= $now) {
                $id = $msg['id'];
//                echo "DAEMON: Ejecutando: $id \n";
                array_shift($this->orderedQueue);
                $this->sortKeys();
                $this->executeTask($id);
            }
        } else {
            //echo "No hay ningun elemento en la lista \n";
        }
    }

    private function executeTask($id) {

        try {

            $dm = MongoDataManager::getDocumentManager();

//            echo "Intentando ejecutar tarea: $id \n";
            $task = $dm->find('MS\Documents\ATask', $id);
            if (isset($task)) {
                if ($task->getExecutionTime() <= microtime(true)) {
                    $task->executeTask();
//                    echo "==> TASK: " . $task->getId() . " executed at: " . date("H:i:s", microtime(true)) . " \n";
                    $dm->remove($task);
                    $dm->flush();
                    echo "==> Tarea con id $id ejecutada y eliminada de BD a las " . date("H:i:s", microtime(true)) . " \n";
                } else {
                    echo "==> TASK: Error: Intento de ejecución antes de tiempo \n";
                }
            } else {
                echo "Error: No existe tarea en base de datos con Id: $id \n";
            }
        } catch (Exception $exc) {
            echo "TASK. Error: " . $exc->getMessage();
        }

//        echo "DAEMON: Mando petición...\n";
//        $urlTask = 'http://localhost/MassiveScheduler/public_files/task.php';
//        $ch = curl_init($urlTask . '?id=' . $id);
//        curl_exec($ch);
//        curl_close($ch);
//        echo "DAEMON: cURL cerrado \n";
    }

    private function sortKeys() {
        array_values($this->orderedQueue);
    }

}

new IPCDaemon();
?>