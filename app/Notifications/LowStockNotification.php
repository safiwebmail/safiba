<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public $item)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'inventory_id' => $this->item->id,
            'message' => "Low stock: {$this->item->name} is at {$this->item->quantity} {$this->item->unit}.",
        ];
    }
}
