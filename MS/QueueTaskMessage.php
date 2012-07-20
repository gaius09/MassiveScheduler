<?php

namespace MS;

class QueueTaskMessage {
    
    const TASK_MESSAGE_ACTION_ADD = 1;
    const TASK_MESSAGE_ACTION_EDIT = 2;
    const TASK_MESSAGE_ACTION_DELETE = 3;

    private $id;
    private $time;
    private $action;

    function __construct($id, $time, $action) {
        $this->id = $id;
        $this->time = $time;
        $this->action = $action;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getTime() {
        return $this->time;
    }
    
    public function getAction() {
        return $this->action;
    }
    
    public function toArray(){
        return array(
            'id' => $this->id,
            'time' => $this->time,
            'action' => $this->action
        );
    }

}

?>
