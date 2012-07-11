<?php

class Task {
    private $horaEjecucion;
    private $mensaje;
    
    public function __construct($horaEjecucion, $mensaje) {
        $this->horaEjecucion = $horaEjecucion;
        $this->mensaje = $mensaje;
    }

    public function getHoraEjecucion() {
        return $this->horaEjecucion;
    }

    public function setHoraEjecucion($horaEjecucion) {
        $this->horaEjecucion = $horaEjecucion;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    public function __toString() {
        return $this->horaEjecucion;
    }

}

?>