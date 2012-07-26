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

    echo "Intentando ejecutar $id \n";
    $task = $dm->find('MS\Documents\ATask', $id);
    if (isset($task)) {
        if ($task->getExecutionTime() <= microtime(true)) {
            $task->executeTask();
            $dm->remove($task);
            $dm->flush();
            echo "ok";
        } else {
            echo "Error: Intento de ejecución antes de tiempo \n";
        }
    } else {
        echo "Error: No existe tarea con este Id \n";
    }
} catch (Exception $exc) {
    echo "Error: " . $exc->getMessage();
}
?>
