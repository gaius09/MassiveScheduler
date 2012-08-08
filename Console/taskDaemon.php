#!/usr/local/bin/php -q

<?php
error_reporting(E_ALL ^ E_NOTICE);

require_once 'System/Daemon.php';

/**
 * Se encarga de recoger los mensajes IPC de las tareas y ejecutarlos
 *  
 * @author Andrés Fernández García
 */
class IPCDaemon {

    const ACTION_ADD = 1;
    const ACTION_EDIT = 2;
    const ACTION_DELETE = 3;
    const TASK_COMMAND_PATH = '/usr/local/bin/php task.php ';
    const LOAD_COMMAND_PATH = '/usr/local/bin/php taskLoader.php ';
    const DAEMON_LOG_DIR = '/var/log/taskdaemon.log';

    private $orderedQueue;
    private $key;
    private $ipc;
    private $verbose;

    public function __construct($verbose) {
        $this->orderedQueue = array();
        $this->key = 987654;
        $this->ipc = msg_get_queue($this->key);
        $this->verbose = $verbose;
        $this->clearQueue();
        $this->loadTasks();
    }

    /**
     * Hace una iteración de recogida de mensaje IPC
     *
     * @return boolean TRUE si no ha habido ningún error
     */
    public function iterate() {
        $message = null;
        $messageType = null;

        while (msg_receive($this->ipc, 0, $messageType, 512 * 1024, $message, TRUE, MSG_IPC_NOWAIT)) {

            if (!isset($message['id'])) {
                System_Daemon::notice('Mensaje sin ID de tarea !!'); //ERROR
            } else {
                if ($message['action'] == self::ACTION_ADD) {
                    // Verbose
                    if ($this->verbose) {
                        System_Daemon::info("Tarea con id " . $message['id'] . " se tiene que ejecutar a las " . date("H:i:s", $message['time']));
                    }

                    array_push($this->orderedQueue, $message);
                } elseif ($message['action'] == self::ACTION_EDIT) {
                    
                } elseif ($message['action'] == self::ACTION_DELETE) {
                    $this->deleteMessageById($message['id']);
                }
                sort($this->orderedQueue);
            }
        }
        $this->executeMessage();

        return true;
    }

    /**
     * Carga todas las tareas en la cola IPC 
     */
    private function loadTasks() {
        try {
            $cmd = IPCDaemon::LOAD_COMMAND_PATH;
            if ($this->verbose) {
                $cmd .= ' --verbose';
                $cmd .= ' >> ' . IPCDaemon::DAEMON_LOG_DIR;
            }
            $arr = array();
            exec($cmd, $arr);
        } catch (Exception $exc) {
            System_Daemon::notice($exc->getMessage());
        }
    }

    /**
     * Borra todos los mensajes de la cola IPC 
     */
    private function clearQueue() {
        $message = null;
        $messageType = null;
        $n = 0;
        while (msg_receive($this->ipc, 0, $messageType, 512 * 1024, $message, TRUE, MSG_IPC_NOWAIT)) {
            $n++;
        }
        System_Daemon::info("Borrados $n mensajes \n");
    }

    /**
     * Borra el mensaje con id pasado por parámetro de la cola ordenada
     * 
     * @param string $id 
     */
    private function deleteMessageById($id) {
        $orderedQueueLength = count($this->orderedQueue);
        for ($index = 0; $index < $orderedQueueLength; $index++) {
            $qMsg = $this->orderedQueue[$index];
            if ($qMsg['id'] == $id) {
                unset($this->orderedQueue[$index]);
                $this->sortKeys();
                if ($this->verbose) {
                    System_Daemon::info('Tarea con id ' . $id . " ha sido borrada");
                }
                break;
            }
        }
    }

    /**
     * Saca el primer mensaje de la cola y ejecuta la tarea
     */
    private function executeMessage() {
        $msg = isset($this->orderedQueue[0]) ? $this->orderedQueue[0] : null;
        if ($msg) {
            $taskTime = $msg['time'];
            $now = microtime(true);
            if ($taskTime <= $now) {
                $id = $msg['id'];
                array_shift($this->orderedQueue);
                $this->sortKeys();
                $this->executeTask($id);
            }
        } else {
//            System_Daemon::notice('No hay ningún elemento en la lista');
        }
    }

    /**
     * Ejecuta la tarea cuyo id se pasa por parámetro
     * 
     * @param type $id 
     */
    private function executeTask($id) {
        try {
            // Ejecutamos el comando php para ejecutar la tarea y redireccionamos la salidda al log
            $cmd = IPCDaemon::TASK_COMMAND_PATH . ' ' . $id;
            if ($this->verbose) {
                $cmd .= ' --verbose';
                $cmd .= ' >> ' . IPCDaemon::DAEMON_LOG_DIR;
            }
            $arr = array();
            exec($cmd, $arr);
        } catch (Exception $exc) {
            System_Daemon::notice($exc->getMessage());
        }
    }

    /**
     * Ordena los índices de la cola ordenada de mensajes
     */
    private function sortKeys() {
        array_values($this->orderedQueue);
    }

}

// Parámetros válidos y sus valores por defecto
$runmode = array(
    'no-daemon' => false,
    'help' => false,
    'write-initd' => false,
    'verbose' => false
);

// Scan command line attributes for allowed arguments
foreach ($argv as $k => $arg) {
    if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
        $runmode[substr($arg, 2)] = true;
    }
}

// Help mode. Shows allowed argumentents and quit directly
if ($runmode['help'] == true) {
    echo 'Usage: ' . $argv[0] . ' [runmode]' . "\n";
    echo 'Available runmodes:' . "\n";
    foreach ($runmode as $runmod => $val) {
        echo ' --' . $runmod . "\n";
    }
    die();
}

// Setup
$options = array(
    'appName' => 'taskdaemon',
    'appDir' => dirname(__FILE__),
    'appDescription' => 'Gestiona tareas programadas',
    'authorName' => 'Andrés Fernández García',
    'authorEmail' => 'propagandagratis@gmail.com',
    'sysMaxExecutionTime' => '0',
    'sysMaxInputTime' => '0',
    'sysMemoryLimit' => '256M',
    'appRunAsGID' => 1000,
    'appRunAsUID' => 1000,
);

System_Daemon::setOptions($options);

// Si estamos en modo background iniciamos el demonio
if (!$runmode['no-daemon']) {
    System_Daemon::start();
}

// Si nos pasan el prámetro write-initd se escribirá el fichero del init.d
if (!$runmode['write-initd']) {
    System_Daemon::info('not writing an init.d script this time');
} else {
    if (($initd_location = System_Daemon::writeAutoRun()) === false) {
        System_Daemon::notice('unable to write init.d script');
    } else {
        System_Daemon::info(
                'sucessfully written startup script: %s', $initd_location
        );
    }
}

$runningOkay = true;
$daemon = new IPCDaemon($runmode['verbose']);

while (!System_Daemon::isDying() && $runningOkay) {
    // What mode are we in?
//    $mode = '"' . (System_Daemon::isInBackground() ? '' : 'non-' ) . 'daemon" mode';
//    System_Daemon::info('{appName} running in %s %s/3', $mode);

    $runningOkay = $daemon->iterate();

    // Podemos devolver false en la función iterate en caso de que se haya 
    // producido un error muy grave que deba hacer que el demonio termine.
    if (!$runningOkay) {
        System_Daemon::err('Error grave');
    }

    // Se llama a unsleep durante el número de segundos que se le pase por 
    // parámetro a la función iterate.
    System_Daemon::iterate(0.1);
}

System_Daemon::stop();
?>