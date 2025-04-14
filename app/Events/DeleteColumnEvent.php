<?php
// app/Events/DeleteColumnEvent.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class DeleteColumnEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $column;

    public function __construct($column)
    {
        $this->column = $column;
    }

    public function broadcastOn()
    {
        return new Channel('project-web');
    }

    public function broadcastAs()
    {
        return 'delete-column';
    }
}
