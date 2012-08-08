#!/usr/local/bin/php -q

<?php
require_once __DIR__ . '/../Loaders/loader.php';

use MS\MongoDataManager,
    MS\Documents\ATask,
    MS\Documents\TaskTest,
    MS\TaskManager;

/**
 * Proporciona funciones de test de tareas
 */
class TaskTester {

    /**
     * Núero de tareas por parte 
     */
    const PART = 1000;

    private $numberOfTasks;

    /**
     * 
     * @param integer Número de tareas que se desean crear para el test 
     */
    function __construct($number) {
        $this->numberOfTasks = $number;
    }

    /**
     * Ejecuta el test
     * 
     * Primero crea el número de taeas que se le pasa por parámetro el constructor
     * en grupos de PART.
     * Luego se añaden todas a la vez a la cola IPC 
     */
    public function run() {
        $partes = intval($this->numberOfTasks / TaskTester::PART);
        $resto = intval($this->numberOfTasks % TaskTester::PART);
        $index = 0;
        $index1 = 0;
        $index2 = 0;
        try {
            echo "Creando tareas... \n";
            $dm = MongoDataManager::getDocumentManager();
            $taskManager = new TaskManager();
            for ($index1 = 0; $index1 < $partes; $index1++) {
                for ($index = 0; $index < TaskTester::PART; $index++) {
                    $duration = mt_rand(300, 7200);
                    $task = new TaskTest($duration);
                    $dm->persist($task);
                }
                $dm->flush();
                echo 'Guardada parte ' . $index1 . "\n";
                 usleep(200000);
            }
            
            $dm->clear();

            $index2 = 0;
            for ($index2 = 0; $index2 < $resto; $index2++) {
                $duration = mt_rand(300, 7200);
                $task = new TaskTest($duration);
                $dm->persist($task);
            }
            $dm->flush();
            
            echo 'Guardado resto: ' . $index2 . "\n";
            
            $dm->clear();

            $tasks = $dm->getRepository('MS\Documents\TaskTest')->findAll();
            if (isset($tasks)) {
                $taskManager = new TaskManager();
                foreach ($tasks as $task) {
                    $taskManager->add($task);
                    usleep(50);
                }
            }

            echo "Tareas guardadas... \n";
        } catch (Exception $exc) {
            echo "Error: " . $exc->getMessage() . "\n";
        }
    }

}


if ($argc < 2) {
    echo "Se debe pasar al menos un argumento... \n";
} else {
    $tr = new TaskTester($argv[1]);
    $tr->run();
}
?>