<?php
require_once '../loader.php';

use MS\MongoDataManager,
    MS\Documents\ATask,
    MS\Documents\TaskAttack,
    MS\Documents\TaskCreateTroop,
    MS\TaskManager,
    MS\Documents\Player;

$dm = MongoDataManager::getDocumentManager();
$player1 = null;
$player2 = null;
$message = null;

var_dump($_REQUEST);
try {
    if (!empty($_POST)) {
        $player1 = $dm->find('MS\Documents\Player', $_REQUEST['player1']);
        $player2 = $dm->find('MS\Documents\Player', $_REQUEST['player2']);
        if (isset($_REQUEST['attack1'])) { // Player 1 ataca
            if (!$player2->isAttacking()) {
                $player1->attack($player2);
            } else {
                $message = 'No puedes atacar si estás siendo atacado...';
            }
        } elseif (isset($_REQUEST['attack2'])) { // Player 2 ataca
            if (!$player1->isAttacking()) {
                $player2->attack($player1);
            } else {
                $message = 'No puedes atacar si estás siendo atacado...';
            }
        } elseif (isset($_REQUEST['cancelAttack1'])) { // Player 1 cancela ataque
            $player1->cancelAttackPlayer();
        } elseif (isset($_REQUEST['cancelAttack2'])) { // Player 2 cancela ataque
            $player2->cancelAttackPlayer();
        } elseif (isset($_REQUEST['trainTroops1'])) { //Player 1 entrena tropas
            $units = $_REQUEST['troopsNumber'];
            $player1->buildTroops($units);
        } elseif (isset($_REQUEST['trainTroops2'])) { //Player 2 entrena tropas
            $units = $_REQUEST['troopsNumber'];
            $player2->buildTroops($units);
        } elseif (isset($_REQUEST['cancelTroop1'])) { //Player 2 entrena tropas
            $taskId = $_REQUEST['taskTroopId'];
            $player1->cancelBuildTroops($taskId);
        } elseif (isset($_REQUEST['cancelTroop2'])) { //Player 2 entrena tropas
            $taskId = $_REQUEST['taskTroopId'];
            $player2->cancelBuildTroops($taskId);
        }
    } else {
        $players = $dm->getRepository('MS\Documents\Player')->findBy(array());
        if (isset($players)) {
            $player2 = $players->getNext();
            $player1 = $players->getNext();
        }

        // Si no existen los creamos
        if (!isset($player1) || !isset($player2)) {
            $player1 = new Player(50, 100);
            $player2 = new Player(40, 80);
            $dm->persist($player1);
            $dm->persist($player2);
            $dm->flush();
        }
    }
} catch (Exception $exc) {
    $message = $exc->getTraceAsString();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Demo</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="css/bootstrap.css"/>
    </head>
    <body>
        <div class="container">
            <h1>Demo</h1>
            <?php if (isset($message)): ?>
                <div class="alert">
                    <button class="close" data-dismiss="alert">×</button>
                    <strong>Alerta!</strong> <?php echo $message ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="span6">
                    <h2>Player 1</h2>
                    <ul>
                        <li>ID: <span class="badge badge-success"><?php echo $player1->getId(); ?></span></li>
                        <li>Oro: <span class="badge badge-success"><?php echo $player1->getGold() ?></span></li>
                        <li>Ejército: <span class="badge badge-successs"><?php echo $player1->getArmy() ?></span></li>
                    </ul>
                    <hr/>
                    <form class="well" name="form1" method="post" action="index.php">
                        <input type="hidden" name="player1" value="<?php echo $player1->getId(); ?>"/>
                        <input type="hidden" name="player2" value="<?php echo $player2->getId(); ?>"/>
                        <?php if (!$player1->isAttacking()): ?>
                            <input class="btn btn-success" type="submit" name="attack1" value="Atacar"/>               
                        <?php else: ?>
                            <input class="btn btn-success" type="submit" name="attack1" value="Atacar" disabled="disabled"/>
                            <br/>
                            <span>Atacando</span>
                            <div class="progress progress-success">
                                <div class="bar"
                                     style="width: 0%;">
                                    <span class="countdowntime" 
                                          data-totalTime="<?php echo round($player1->getTaskAttack()->getDuration()) ?>" 
                                          data-currentTime="<?php echo round($player1->getTaskAttack()->getRemainingTime()) ?>">
                                    </span>
                                </div>
                            </div>
                            <input class="btn btn-danger pull-right" type="submit" name="cancelAttack1" value="Cancelar"/>
                            <div class="clearfix"></div>
                        <?php endif; ?>
                    </form>
                    <form class="well form-inline" name="" method="post" action="index.php">
                        <input type="hidden" name="player1" value="<?php echo $player1->getId(); ?>"/>
                        <input type="hidden" name="player2" value="<?php echo $player2->getId(); ?>"/>
                        <div>
                            <label>Entrenar tropas: </label>
                            <input type="text" class="input-small" name="troopsNumber">
                            <input class="btn btn-success" type="submit" name="trainTroops1" value="Entrenar"/>
                        </div>
                    </form>
                    <?php foreach ($player1->getTaskCreateTroopCollection() as $taskCT): ?>
                        <form class="well" name="" method="post" action="index.php">
                            <input type="hidden" name="player1" value="<?php echo $player1->getId(); ?>"/>
                            <input type="hidden" name="player2" value="<?php echo $player2->getId(); ?>"/>
                             <span class="help-block">Entrenando <?php echo $taskCT->getUnits() ?> tropas...</span>
                            <div class="progress progress-success progress-striped active">
                                <div class="bar"
                                     style="width: 0%;">
                                    <span class="countdowntime" 
                                          data-totalTime="<?php echo round($taskCT->getDuration()) ?>" 
                                          data-currentTime="<?php echo round($taskCT->getRemainingTime()) ?>">
                                    </span>
                                </div>
                            </div>
                            <input class="btn btn-danger pull-right" type="submit" name="cancelTroop1" value="Cancelar"/>
                            <input type="hidden" name="taskTroopId" value="<?php echo $taskCT->getId() ?>"/>
                            <div class="clearfix"></div>  
                        </form>
                    <?php endforeach; ?>

                </div>
                <div class="span6">...</div>
            </div>


        </div>
        <script src="js/jquery-1.7.2.js"></script>
        <script src="js/bootstrap.js"></script>
        <script src="js/jquery.timer.js"></script>
        <script>
            function countDown(jQobject){
                var totalTime = jQobject.attr('data-totalTime');
                var currentTime = jQobject.attr('data-currentTime');
                var startPercent =  100 - Math.round((currentTime * 100) / totalTime);
                jQobject.parent().width(startPercent +'%');
                
                var countdownCurrent = currentTime;
                var countdownTimer = $.timer(function() {
                    var percent = 100 - Math.round((countdownCurrent * 100) / totalTime);
                    var horas = parseInt(countdownCurrent / 3600);
                    var minutos = parseInt(countdownCurrent / 60) - (horas * 60);
                    var segundos = pad(countdownCurrent - (minutos * 60) - (horas * 3600), 2);
                    var output = "00"; 
                    if(horas > 0) {output = pad(horas,2);}
                    jQobject.html(output + ":" + pad(minutos, 2) + ":" + segundos);
                    if(countdownCurrent == 0) {
                        countdownTimer.stop();
                        //window.location.reload(false); 
                    } else {
                        countdownCurrent-=1;
                        if(countdownCurrent < 0) {countdownCurrent = 0;}
                    }
                    jQobject.parent().width(percent +'%');
                }, 1000, true);
                
                // Padding function
                var pad = function (number, length) {
                    var str = '' + number;
                    while (str.length < length) {str = '0' + str;}
                    return str;
                }
            }
            
            $(document).ready(function(){
                $('.countdowntime').each(function(){
                    countDown($(this));
                });
            });
            
            
        </script>
    </body>
</html>