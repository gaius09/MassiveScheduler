<?php

require_once '../loader.php';

use MS\MongoDataManager,
    MS\Documents\ATask,
    MS\QueueTaskMessage;

$id;

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
} else {
    throw new \Exception('Falta parámetro id');
}

try {

    $dm = MongoDataManager::getDocumentManager();

    echo "TASK: Intentando ejecutar $id \n";
    $task = $dm->find('MS\Documents\ATask', $id);
    if (isset($task)) {
        if ($task->getExecutionTime() <= microtime(true)) {
            $task->executeTask();
            echo "==TASK: " . $task->getId() . " executed at: " . date("H:i:s", microtime(true)) . " \n";
            $dm->remove($task);
            $dm->flush();
            echo "TASK: ok\n";
        } else {
            echo "TASK: Error: Intento de ejecución antes de tiempo \n";
        }
    } else {
        echo "TASK: Error: No existe tarea con este Id \n";
    }
} catch (Exception $exc) {
    echo "TASK. Error: " . $exc->getMessage();
}
?>
