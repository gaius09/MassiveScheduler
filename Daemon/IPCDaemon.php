#!/usr/local/bin/php -q

<?php
// Include Class
//error_reporting(E_STRICT);
require_once 'System/Daemon.php';

class IPCDaemon {

    const ACTION_ADD = 1;
    const ACTION_EDIT = 2;
    const ACTION_DELETE = 3;
    const TASK_COMMAND_PATH = '/usr/local/bin/php task.php';

    private $orderedQueue;
    private $key;
    private $ipc;

    public function __construct() {
        $this->orderedQueue = array();
        $this->key = 987654;
        $this->ipc = msg_get_queue($this->key);
//        $this->init();
    }

    public function iterate() {
//        System_Daemon::info("DAEMON: Ejecutando... \n");

        $message = null;
        $messageType = null;

//        while (true) {
//            usleep(10000);
        while (msg_receive($this->ipc, 0, $messageType, 512 * 1024, $message, TRUE, MSG_IPC_NOWAIT)) {
//                echo "DAEMON: Mensaje recibido \n";
            //        var_dump($message);

            if ($message['action'] == self::ACTION_ADD) {
                System_Daemon::info("Tarea con id " . $message['id'] . " se tiene que ejecutar a las :" . date("H:i:s", $message['time']));
                array_push($this->orderedQueue, $message);
            } elseif ($message['action'] == self::ACTION_EDIT) {
                
            } elseif ($message['action'] == self::ACTION_DELETE) {
                $this->deleteMessageById($message['id']);
            }
            sort($this->orderedQueue);

//      var_dump($orderedQueue);
        }
        $this->executeMessage();
//        }
    }

    private function deleteMessageById($id) {
        $orderedQueueLength = count($this->orderedQueue);
        for ($index = 0; $index < $orderedQueueLength; $index++) {
            $qMsg = $this->orderedQueue[$index];
            if ($qMsg['id'] == $id) {
                unset($this->orderedQueue[$index]);
                array_values($this->orderedQueue);
                System_Daemon::info('Tarea con id ' . $id . " ha sido borrada");
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
//            System_Daemon::info(IPCDaemon::TASK_COMMAND_PATH . ' ' . $id);
            system(IPCDaemon::TASK_COMMAND_PATH . ' ' . $id);
//            system('tail task.php');
        } catch (Exception $exc) {
            System_Daemon::notice($exc->getMessage());
        }

        
    }

    private function sortKeys() {
        array_values($this->orderedQueue);
    }

}

/**
 * @category  System
 * @package   System_Daemon
 * @author    Andrés Fernández
 */
/**
 * System_Daemon Example Code
 *
 * If you run this code successfully, a daemon will be spawned
 * but unless have already generated the init.d script, you have
 * no real way of killing it yet.
 *
 * In this case wait 3 runs, which is the maximum for this example.
 *
 *
 * In panic situations, you can always kill you daemon by typing
 *
 * killall -9 logparser.php
 * OR:
 * killall -9 php
 *
 */
// Allowed arguments & their defaults
$runmode = array(
    'no-daemon' => false,
    'help' => false,
    'write-initd' => false,
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

// This program can also be run in the forground with runmode --no-daemon
if (!$runmode['no-daemon']) {
    // Spawn Daemon
    System_Daemon::start();
}

// With the runmode --write-initd, this program can automatically write a
// system startup file called: 'init.d'
// This will make sure your daemon will be started on reboot
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

// Run your code
// Here comes your own actual code
// This variable gives your own code the ability to breakdown the daemon:
$runningOkay = true;
$daemon = new IPCDaemon();

// While checks on 3 things in this case:
// - That the Daemon Class hasn't reported it's dying
// - That your own code has been running Okay
// - That we're not executing more than 3 runs
while (!System_Daemon::isDying() && $runningOkay) {
    // What mode are we in?
    $mode = '"' . (System_Daemon::isInBackground() ? '' : 'non-' ) .
            'daemon" mode';

    // Log something using the Daemon class's logging facility
    // Depending on runmode it will either end up:
    //  - In the /var/log/logparser.log
    //  - On screen (in case we're not a daemon yet)
//    System_Daemon::info('{appName} running in %s %s/3', $mode);

    // In the actuall logparser program, You could replace 'true'
    // With e.g. a  parseLog('vsftpd') function, and have it return
    // either true on success, or false on failure.
    $runningOkay = true;
    $daemon->iterate();


    //$runningOkay = parseLog('vsftpd');
    // Should your parseLog('vsftpd') return false, then
    // the daemon is automatically shut down.
    // An extra log entry would be nice, we're using level 3,
    // which is critical.
    // Level 4 would be fatal and shuts down the daemon immediately,
    // which in this case is handled by the while condition.
    if (!$runningOkay) {
        System_Daemon::err('parseLog() produced an error, ' .
                'so this will be my last run');
    }

    // Relax the system by sleeping for a little bit
    // iterate also clears statcache
    System_Daemon::iterate(0.1);
}

// Shut down the daemon nicely
// This is ignored if the class is actually running in the foreground
System_Daemon::stop();
?>