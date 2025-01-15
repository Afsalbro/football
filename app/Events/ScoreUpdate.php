<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $matchData;

    public function __construct(array $matchData)
    {
        $this->matchData = $matchData;
    }

    public function broadcastOn()
    {
        return new Channel('football-match');
    }

    public function broadcastAs()
    {
        return 'score.update';
    }
}