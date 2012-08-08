#!/usr/local/bin/php -q

<?php

require_once __DIR__ . '/../Loaders/loader.php';

use MS\MongoDataManager;
use MS\Documents\ATask;

/**
 * Se encarga de ejecutar una tarea
 */
class TaskRunner {

    private $id;
    private $verbose;

    /**
     * 
     * @param string ID de la tarea 
     * @param boolean Si es TRUE, se escribirán sucesos lor la salida estándar
     */
    function __construct($id, $verbose) {
        $this->id = $id;
        $this->verbose = $verbose;
    }

    /**
     * Ejecuta la tarea y la borra de la base de datos
     */
    public function run() {
        try {
            $id = $this->id;

            $dm = MongoDataManager::getDocumentManager();

            $task = $dm->find('MS\Documents\ATask', $id);
            if (isset($task)) {
                if ($task->getExecutionTime() <= microtime(true)) {
                    $task->executeTask();
                    $dm->remove($task);
                    $dm->flush();
                    if($this->verbose) echo "Tarea con id $id ejecutada y eliminada de BD a las " . date("H:i:s", microtime(true));
                } else {
                    if($this->verbose) echo "==> TASK: Error: Intento de ejecución antes de tiempo \n";
                }
            } else {
                if($this->verbose) echo "Error: No existe tarea en base de datos con Id: $id \n";
            }
        } catch (Exception $exc) {
            if($this->verbose) echo "TASK. Error: " . $exc->getMessage();
        }
    }

}

// Parámetros válidos y sus valores por defecto
$runmode = array(
    'verbose' => false
);

// Scan command line attributes for allowed arguments
foreach ($argv as $k => $arg) {
    if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
        $runmode[substr($arg, 2)] = true;
    }
}

if ($argc < 2) {
    echo "Se debe pasar al menos un argumento... \n";
} else {
    $tr = new TaskRunner($argv[1], $runmode['verbose']);
    $tr->run();
}
?>
