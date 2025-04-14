<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AddCardEvent implements ShouldBroadcast
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
        return 'add-card';
    }
}
