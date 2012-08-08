#!/usr/local/bin/php -q

<?php
require_once __DIR__ . '/../Loaders/loader.php';

use MS\MongoDataManager,
    MS\Documents\ATask,
    MS\TaskManager;

/**
 * Representa un cargador de tareas
 */
class TaskLoader {

    function __construct() {
        
    }

    /**
     * Carga todas la tareas de la base de datos en la cola IPC
     */
    public function loadAllTasks() {
        $tasks = null;
        try {
            $dm = MongoDataManager::getDocumentManager();

            $tasks = $dm->getRepository('MS\Documents\ATask')->findAll();
            if (isset($tasks)) {
                $taskManager = new TaskManager();
                foreach ($tasks as $task) {
                    $taskManager->add($task);
                }
            } else {
                echo "TASK LOADER. No hay tareas en la base de datos \n";
            }
        } catch (Exception $exc) {
            echo "TASK LOADER. Error: " . $exc->getMessage() . "\n";
        }

        echo 'TASK LOADER. Tareas cargadas: ' . count($tasks) . "\n";
    }
}

$tl = new TaskLoader();
$tl->loadAllTasks();
?>
