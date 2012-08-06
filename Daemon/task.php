#!/usr/local/bin/php -q

<?php
    
require_once __DIR__ . '/../Loaders/loader.php';

use MS\MongoDataManager;
use MS\Documents\ATask;

class TaskRunner {

    private $id;

    function __construct($id) {
        $this->id = $id;
    }

    public function run() {
        try {
            $id = $this->id;

            $dm = MongoDataManager::getDocumentManager();

//            echo "Intentando ejecutar tarea: $id \n";
            $task = $dm->find('MS\Documents\ATask', $id);
            if (isset($task)) {
                if ($task->getExecutionTime() <= microtime(true)) {
                    $task->executeTask();
//                    echo "==> TASK: " . $task->getId() . " executed at: " . date("H:i:s", microtime(true)) . " \n";
                    $dm->remove($task);
                    $dm->flush();
//                    echo "==> Tarea con id $id ejecutada y eliminada de BD a las " . date("H:i:s", microtime(true)) . " \n";
                } else {
//                    echo "==> TASK: Error: Intento de ejecución antes de tiempo \n";
                }
            } else {
//                echo "Error: No existe tarea en base de datos con Id: $id \n";
            }
        } catch (Exception $exc) {
//            echo "TASK. Error: " . $exc->getMessage();
        }
    }

}

/*
 *  MODO WEB
 */
//if (isset($_REQUEST['id'])) {
//    $id = $_REQUEST['id'];
//} else {
//    throw new \Exception('Falta parámetro id');
//}

/*
 * MODO LINEA DE COMANDOS
 */
//var_dump($_SERVER["argv"]);
if ($argc != 2) {
//    echo "Se debe pasar un solo argumento... \n";
} else {
    $tr = new TaskRunner($argv[1]);
    $tr->run();
}
?>
