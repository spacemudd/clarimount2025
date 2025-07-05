<?php

namespace App\Events;

use App\Models\PrintJob;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PrintJob $printJob;

    /**
     * Create a new event instance.
     */
    public function __construct(PrintJob $printJob)
    {
        $this->printJob = $printJob;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('print-station'),
            new PrivateChannel('company.' . $this->printJob->company_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->printJob->id,
            'job_id' => $this->printJob->job_id,
            'asset' => [
                'id' => $this->printJob->asset->id,
                'asset_tag' => $this->printJob->asset->asset_tag,
                'serial_number' => $this->printJob->asset->serial_number,
                'model_name' => $this->printJob->asset->model_name,
                'category' => $this->printJob->asset->category->name ?? null,
            ],
            'company' => [
                'id' => $this->printJob->company->id,
                'name' => $this->printJob->company->name_en,
            ],
            'user' => [
                'id' => $this->printJob->user->id,
                'name' => $this->printJob->user->name,
            ],
            'priority' => $this->printJob->priority,
            'status' => $this->printJob->status,
            'print_data' => $this->printJob->print_data,
            'requested_at' => $this->printJob->requested_at->toISOString(),
            'created_at' => $this->printJob->created_at->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'print-job.created';
    }
}
