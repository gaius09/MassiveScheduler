<?php

require_once 'loader.php';

use MS\ODM,
    MS\Documents\ATask,
    MS\QueueTaskMessage;

$id;

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
} else {
    throw new \Exception('Falta parámetro id');
}

try {

    $dm = ODM::getDocumentManager();

    $task = $dm->find('MS\Documents\ATask', $id);
    if (isset($task)) {
        if ($task->getTime() <= microtime(true)) {
            $task->executeTask();
            $dm->remove($task);
            $dm->flush();

            echo 'ok';
        } else {
            echo 'Error: Intento de ejecución antes de tiempo';
        }
    } else {
        echo 'Error: No existe tarea con este Id';
    }
} catch (Exception $exc) {
    echo 'Error: ' . $exc->getMessage();
}
?>
