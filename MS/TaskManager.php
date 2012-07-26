<?php

namespace MS;

use MS\QueueTaskMessage;

class TaskManager {

    private $key = 987654;
    private $IPCQueue;
    private $messageType = 1;

    function __construct() {
        $this->IPCQueue = msg_get_queue($this->key);
    }

    private function send(QueueTaskMessage $msg) {
        msg_send($this->IPCQueue, $this->messageType, $msg->toArray());
    }

    public function add(ITask $task) {
        $q = new QueueTaskMessage($task->getId(), $task->getExecutionTime(), QueueTaskMessage::TASK_MESSAGE_ACTION_ADD);
        $this->send($q);
    }

    public function edit(ITask $task) {
        $this->send(new QueueTaskMessage($task->getId(), $task->getExecutionTime(), QueueTaskMessage::TASK_MESSAGE_ACTION_EDIT));
    }

    public function remove(ITask $task) {
        $this->send(new QueueTaskMessage($task->getId(), $task->getExecutionTime(), QueueTaskMessage::TASK_MESSAGE_ACTION_DELETE));
    }

}

?>
