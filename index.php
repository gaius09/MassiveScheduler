<?php
require_once 'loader.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php

        use MS\ODM,
            MS\Documents\ATask,
            MS\Documents\TaskAttack,
            MS\Documents\TaskCreateTroop,
            MS\TaskManager;

//try {

            $dm = ODM::getDocumentManager();
            
            $attack = new TaskAttack(microtime(true) + 30, 'pepe');
//            $troop = new TaskCreateTroop(1342629195.7244, 5);
            $dm->persist($attack);
//            $dm->persist($troop);
//            
            $dm->flush();

            
//            $taskManager->add($troop);
//            
//            $tarea = $dm->find('MS\Documents\ATask', "500935fb6e87ddd50c000001");
//            echo $tarea->getQueueMessage()->getId() . '<br>';
//            echo $tarea->getQueueMessage()->getTime() . '<br>';
//            $sub = $dm->find('Documents\Subject', "4fed68046e87dd3d07000002");
            
           $taskManager = new TaskManager();
//            $taskManager->remove($tarea);
            $taskManager->add($attack);
           
//    $users = $dm->getRepository('Documents\User')->findBy(array('name' => 'Juan'));
//    foreach ($users as $user) {
//        var_dump($user);
//    }
//        } catch (Exception $exc) {
//            echo '<h3>Error</h3>';
//            echo $exc->getMessage();
//        }
        ?>
    </body>
</html>
