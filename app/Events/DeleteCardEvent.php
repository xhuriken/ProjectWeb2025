<?php
// app/Events/DeleteCardEvent.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class DeleteCardEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $card;

    public function __construct($card)
    {
        $this->card = $card;
    }

    public function broadcastOn()
    {
        return new Channel('project-web');
    }

    public function broadcastAs()
    {
        return 'delete-card';
    }
}
