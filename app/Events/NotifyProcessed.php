<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Log the channel being broadcasted to
        Log::info('Broadcasting on channel:', ['channel' => 'send_notify_skincare']);

        return new Channel('send_notify_skincare');
    }

    public function broadcastAs()
    {
        // Log the event name being broadcasted
        Log::info('Broadcasting event as:', ['event' => 'my-notify']);

        return 'my-notify';
    }
}
