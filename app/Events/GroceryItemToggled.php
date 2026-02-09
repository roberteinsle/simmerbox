<?php

namespace App\Events;

use App\Models\GroceryItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroceryItemToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $groceryItemId,
        public bool $isChecked,
        public int $householdId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('household.' . $this->householdId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'grocery_item_id' => $this->groceryItemId,
            'is_checked' => $this->isChecked,
        ];
    }
}
